<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Tache;
use App\Models\Activite;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SessionActivite;
use App\Models\NotificationActivite;
use App\Models\PieceJustificative;
use Illuminate\Support\Facades\Storage;
use App\Models\Structure;
class TacheController extends Controller
{
    /**
     * Vérifie si la session associée à l'activité d'une tâche est clôturée.
     */
    private function verifySessionNotClosed($activiteId)
    {
        $activite = Activite::find($activiteId);
        if (!$activite) return;

        $session = SessionActivite::find($activite->sessions_id);
        if ($session && $session->etat === 'Clôturé') {
            throw new \Exception("Cette session est clôturée. Toute modification est interdite.");
        }
    }

    /**
     * Ajouter une nouvelle tâche à une activité.
     */
    public function store(Request $request, $activiteId)
    {
        // Valider les données
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
            'pourcentage_tache' => 'required|integer|min:0|max:100',
        ]);

        // Vérifier si l'activité existe
        $activite = Activite::findOrFail($activiteId);
        try {
            $this->verifySessionNotClosed($activiteId);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        // Vérifier si une tâche avec le même libellé existe déjà pour cette activité
        $existeDeja = Tache::where('activite_id', $activite->id)
            ->where('libelle', $validated['libelle'])
            ->exists();

        if ($existeDeja) {
            return response()->json([
                'message' => 'Une tâche avec ce libellé existe déjà pour cette activité.',
            ], 422);
        }

        // Vérifier que le total des pourcentages ne dépasse pas 100%
        $totalPourcentage = Tache::where('activite_id', $activite->id)->sum('pourcentage_tache');
        if (($totalPourcentage + $validated['pourcentage_tache']) > 100) {
            return response()->json([
                'message' => 'Le total des pourcentages des tâches ne peut pas dépasser 100%. Restant : ' . (100 - $totalPourcentage) . '%.',
            ], 422);
        }

        // Ajouter la tâche
        $tache = new Tache();
        $tache->libelle = $validated['libelle'];
        $tache->pourcentage_tache = $validated['pourcentage_tache'];
        $tache->etat = 0; // État par défaut pour une nouvelle tâche
        $tache->activite_id = $activite->id;

        $tache->save();
        //Metter à jour l'etat de l'activité
        $this->mettreAJourEtatActivite($tache->activite_id);
        // Réponse JSON
        return response()->json([
            'message' => 'Tâche ajoutée avec succès.',
            'tache' => $tache,
        ], 201);
    }

    public function getActivite($id)
{
    $activite = Activite::with(['taches' => function ($query) {
        $query->orderBy('id', 'asc')->with('piecesJustificatives');
    }])->findOrFail($id);

    return response()->json([
        'etat_financier' => $activite->etat_financier,
        'financement' => ($activite->finance_partenaire+$activite->finance_etat), // Exemple
        'finance_etat' => $activite->finance_etat, // Exemple
        'finance_partenaire'=>$activite->finance_partenaire,
        'etat'=>$activite->etat,
        'etat_activite' => $activite->etat_activite,
        'taches' => $activite->taches,
    ]);
}

    public function modifierTache(Request $request, $id)
    {
        Log::info("Début de la modification de la tâche", ['id' => $id]);

        // Validation des données
        $request->validate([
            'libelle' => 'required|string|max:255',
            'pourcentage_tache' => 'required|integer|min:0|max:100',
            'taux_execution_tache' => 'required|integer|min:0|max:100',
            //'etat' => 'required|boolean',
        ]);

        // Recherche de la tâche par son ID
        $tache = Tache::findOrFail($id);
        try {
            $this->verifySessionNotClosed($tache->activite_id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
        Log::info("Tâche trouvée", ['id' => $id]);

        DB::beginTransaction(); // Début de transaction

        try {
            // Vérifier que le total des pourcentages ne dépasse pas 100% lors de la modification
            $totalActuel = Tache::where('activite_id', $tache->activite_id)
                ->where('id', '!=', $id)
                ->sum('pourcentage_tache');
            
            if (($totalActuel + $request->pourcentage_tache) > 100) {
                return response()->json([
                    'message' => 'Le total des pourcentages des tâches ne peut pas dépasser 100%. Restant possible : ' . (100 - $totalActuel) . '%.',
                ], 422);
            }

            // Sauvegarder l'ancienne valeur du taux d'exécution pour le log (si nécessaire)
            $ancienneTauxExecution = $tache->taux_execution_tache;
            $ancienneEtat=$tache->etat;
            // Mise à jour des champs
            $tache->libelle = $request->libelle;
            $tache->pourcentage_tache = (int)$request->pourcentage_tache;
            $taux_execution = (int)abs($request->taux_execution_tache); // Transformer en valeur positive

            // Calculer la différence (delta) entre le nouveau taux et l'ancien taux d'exécution actuelle
            $deltaTaux = $taux_execution - (int)$tache->taux_execution_tache;

            // Vérifier si cette modification va terminer l'activité (100%)
            if ($taux_execution >= (int)$request->pourcentage_tache) {
                $activiteParente = Activite::findOrFail($tache->activite_id);
                $autresTachesNonTerminees = Tache::where('activite_id', $activiteParente->id)
                    ->where('id', '!=', $id)
                    ->where(function($query) {
                        $query->where('etat', 0)->orWhereNull('etat');
                    })
                    ->exists();

                if (!$autresTachesNonTerminees && (is_null($activiteParente->etat_financier) || $activiteParente->etat_financier <= 0)) {
                    return response()->json([
                        'requires_finance' => true,
                        'message' => 'Veuillez renseigner l\'État Financier (Dépenses) de l\'activité avant de la valider.',
                    ], 422);
                }
            }

            // Mise à jour de l'état de la tâche
            if ($taux_execution >= $tache->pourcentage_tache) {
                $taux_execution = $tache->pourcentage_tache; // Limiter au pourcentage de la tâche
                $tache->etat = 1;
            } else {
                $tache->etat = 0; // Remettre en-cours si le taux est réduit (<100%)
            }
            
            $tache->taux_execution_tache = $taux_execution;
            $tache->save();

            // Vérifier si toutes les tâches associées à l'activité sont terminées
            $this->mettreAJourEtatActivite($tache->activite_id);

            // Récupérer l'activité parente pour mettre à jour ses statistiques trimestrielles
            $activite = $tache->activite;

            // Calculer le nombre de trimestres programmés
            $nbTrimestres = 0;
            if ($activite->trimestre_1) $nbTrimestres++;
            if ($activite->trimestre_2) $nbTrimestres++;
            if ($activite->trimestre_3) $nbTrimestres++;
            if ($activite->trimestre_4) $nbTrimestres++;

            // Mise à jour du taux d'exécution réparti sur les trimestres programmés
            if ($nbTrimestres > 0) {
                $deltaParTrimestre = round($deltaTaux / $nbTrimestres);
                
                if ($activite->trimestre_1) {
                    $activite->taux_t1 += $deltaParTrimestre;
                    $activite->taux_t1 = max(0, $activite->taux_t1);
                }
                if ($activite->trimestre_2) {
                    $activite->taux_t2 += $deltaParTrimestre;
                    $activite->taux_t2 = max(0, $activite->taux_t2);
                }
                if ($activite->trimestre_3) {
                    $activite->taux_t3 += $deltaParTrimestre;
                    $activite->taux_t3 = max(0, $activite->taux_t3);
                }
                if ($activite->trimestre_4) {
                    $activite->taux_t4 += $deltaParTrimestre;
                    $activite->taux_t4 = max(0, $activite->taux_t4);
                }
            }
            Log::info("Mise à jour des champs effectuée", [
                'ancienne_taux_execution' => $ancienneTauxExecution,
                'nouveau_taux_execution' => $tache->taux_execution_tache,
            ]);

            // Enregistrer toutes les modifications
            $activite->save();
            DB::commit(); // Valider la transaction

            // Mettre à jour l'état de l'activité (en-cours, terminer, etc.)
            $this->mettreAJourEtatActivite($activite->id);

            Log::info("Modification de la tâche réussie", ['id' => $id]);

            // Retourner une réponse JSON
            return response()->json([
                'message' => 'Tâche mise à jour avec succès.',
                'tache' => $tache,
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Annuler la transaction en cas d'erreur
            Log::error("Erreur lors de la modification de la tâche", [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour.',
                'error' => $e->getMessage(),
            ], 500);
        }
        // Retourne une réponse JSON
    
    }

    private function mettreAJourEtatActivite($activite_id)
{
    $activite = Activite::findOrFail($activite_id);

    $toutesTachesTerminees = Tache::where('activite_id', $activite_id)
        ->where(function($query) {
            $query->where('etat', 0)->orWhereNull('etat');
        })
        ->doesntExist();

    $session = SessionActivite::find($activite->sessions_id);

    $sommeTauxExecution = Tache::where('activite_id', $activite_id)->sum('taux_execution_tache');

    \Log::info("Activité ID: $activite_id, Somme Taux: $sommeTauxExecution, Toutes tâches terminées: " . ($toutesTachesTerminees ? 'Oui' : 'Non'));
    \Log::info("Session état: " . ($session ? $session->etat : 'N/A'));
    \Log::info("Reconduir: " . ($activite->reconduir ?? 'N/A'));

    if ($session && $session->etat == 'terminer' && is_null($activite->reconduir)) {
        $nouvelEtat = 'inachever';
    } elseif ($toutesTachesTerminees && count($activite->taches) > 0) {
        $nouvelEtat = 'terminer';
         // Récupérer tous les utilisateurs dont le rôle est 'Administrateur' ou 'Invité'
        $utilisateurs = User::whereIn('role', ['Administrateur', 'Ordonnateur','Chef-de-service'])->get();

        // Créer une notification pour chaque utilisateur
        foreach ($utilisateurs as $user) {
            NotificationActivite::create([
                'message'     => "L'activité '{$activite->libelle}' a été terminée.",
                'lu'          => 0,  // Notification non lue par défaut
                'user_id'     => $user->id,
                'activite_id' => $activite->id,
            ]);
        }
    } elseif ($activite->confirmation_presi == 1) {
        $nouvelEtat = 'en-cours';
    } else {
        $nouvelEtat = 'en-attente';
    }

    \Log::info("Nouvel état prévu : $nouvelEtat");

    if ($activite->etat_activite !== $nouvelEtat) {
        $activite->etat_activite = $nouvelEtat;
        $activite->updated_at = now(); // Force l'update
        $activite->save();
        \Log::info("Activité mise à jour avec succès : " . $activite->etat_activite);
    } else {
        \Log::info("Aucune mise à jour nécessaire.");
    }
}

public function destroy($id)
{
    try {
        \Log::info('Début de la méthode destroy pour la tâche.', ['tache_id' => $id]);

        // Vérification de l'existence de la tâche
        $tache = Tache::find($id);
        if (!$tache) {
            \Log::warning('Tâche introuvable.', ['tache_id' => $id]);
            return response()->json([
                'message' => 'Tâche introuvable.',
            ], 404);
        }

        try {
            $this->verifySessionNotClosed($tache->activite_id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        \Log::info('Suppression de la tâche.', ['tache_id' => $id]);
        $tache->delete();

        \Log::info('Tâche supprimée avec succès.', ['tache_id' => $id]);
        return response()->json([
            'message' => 'Tâche supprimée avec succès.',
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Une erreur est survenue lors de la suppression de la tâche.', [
            'exception_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'message' => 'Une erreur est survenue lors de la suppression de la tâche.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function getTachesByActivite($activiteId)
    {
        $taches = Tache::where('activite_id', $activiteId)
            ->with('piecesJustificatives')
            ->get();
        return response()->json($taches);
    }

    /**
     * Ajouter une pièce justificative à une tâche.
     */
    public function ajouterPieceJustificative(Request $request, $tacheId)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
        ]);

        $tache = Tache::findOrFail($tacheId);
        try {
            $this->verifySessionNotClosed($tache->activite_id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        $activite = Activite::findOrFail($tache->activite_id);
        $session = SessionActivite::find($activite->sessions_id);
        $structure = Structure::find($activite->structures_id);

        $nomSession = $session ? str_replace([' ', '/', '\\'], '_', $session->annee ?? 'Session') : 'Session_Inconnue';
        $nomStructure = $structure ? str_replace([' ', '/', '\\'], '_', $structure->sigle ?? $structure->libelle_structure) : 'Structure_Inconnue';

        $dossier = 'pieces_justificatives/Session_' . $nomSession . '/' . $nomStructure . '_' . $activite->id;

        $fichier = $request->file('fichier');
        $nomFichier = time() . '_' . $fichier->getClientOriginalName();
        $chemin = $fichier->storeAs($dossier, $nomFichier, 'public');

        $piece = PieceJustificative::create([
            'nom_fichier' => $fichier->getClientOriginalName(),
            'chemin_fichier' => $chemin,
            'type_fichier' => $fichier->getClientMimeType(),
            'taille' => $fichier->getSize(),
            'tache_id' => $tache->id,
        ]);

        return response()->json([
            'message' => 'Pièce justificative ajoutée avec succès.',
            'piece' => $piece,
        ], 201);
    }

    /**
     * Supprimer une pièce justificative.
     */
    public function supprimerPieceJustificative($pieceId)
    {
        $piece = PieceJustificative::findOrFail($pieceId);
        $tache = Tache::findOrFail($piece->tache_id);
        try {
            $this->verifySessionNotClosed($tache->activite_id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        // Supprimer le fichier du stockage
        Storage::disk('public')->delete($piece->chemin_fichier);

        $piece->delete();

        return response()->json([
            'message' => 'Pièce justificative supprimée avec succès.',
        ]);
    }

    /**
     * Lister les pièces justificatives d'une tâche.
     */
    public function getPiecesJustificatives($tacheId)
    {
        $pieces = PieceJustificative::where('tache_id', $tacheId)->get();
        return response()->json($pieces);
    }
}
