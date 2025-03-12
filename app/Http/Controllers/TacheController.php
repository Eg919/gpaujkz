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
class TacheController extends Controller
{
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
    $activite = Activite::with('taches')->findOrFail($id);

    return response()->json([
        'etat_financier' => $activite->etat_financier,
        'financement' => ($activite->finance_partenaire+$activite->finance_etat), // Exemple
        'finance_etat' => $activite->finance_etat, // Exemple
        'finance_partenaire'=>$activite->finance_partenaire,
        'etat'=>$activite->etat,
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
        Log::info("Tâche trouvée", ['id' => $id]);

        DB::beginTransaction(); // Début de transaction

        try {
            // Sauvegarder l'ancienne valeur du taux d'exécution pour le log (si nécessaire)
            $ancienneTauxExecution = $tache->taux_execution_tache;
            $ancienneEtat=$tache->etat;
            // Mise à jour des champs
            $tache->libelle = $request->libelle;
            $tache->pourcentage_tache = $request->pourcentage_tache;
            $taux_execution = abs($request->taux_execution_tache); // Transformer en valeur positive

            if ($taux_execution >= $tache->pourcentage_tache) {
                $taux_execution = $tache->pourcentage_tache; // Limiter au pourcentage de la tâche
                $tache->etat = 1;
            }
            $tache->taux_execution_tache = $taux_execution;
            $tache->save();
            // Vérifier si toutes les tâches associées à l'activité sont terminées
            $this->mettreAJourEtatActivite($tache->activite_id);
            // Récupérer le trimestre actuel
            $activite = Tache::with('activite')->findOrFail($id)->activite;

            $now = Carbon::now();
            $trimestre = ceil($now->month / 3);
            Log::info("Trimestre courant déterminé", ['mois' => $now->month, 'trimestre' => $trimestre]);

            // Mise à jour du taux d'exécution du trimestre correspondant
            // Ici, on ajoute le nouveau taux d'exécution. Adaptez la logique si vous devez effectuer un calcul différent
            if($ancienneEtat== 0 ){
            switch ($trimestre) {
                case 1:
                    $activite->taux_t1 += $tache->taux_execution_tache;
                    break;
                case 2:
                    $activite->taux_t2 += $tache->taux_execution_tache;
                    break;
                case 3:
                    $activite->taux_t3 += $tache->taux_execution_tache;
                    break;
                case 4:
                    $activite->taux_t4 += $tache->taux_execution_tache;
                    break;
                default:
                    Log::warning("Trimestre inconnu", ['trimestre' => $trimestre]);
                    break;
            }
        }
            Log::info("Mise à jour des champs effectuée", [
                'ancienne_taux_execution' => $ancienneTauxExecution,
                'nouveau_taux_execution' => $tache->taux_execution_tache,
            ]);

            // Enregistrer toutes les modifications
            $activite->save();
            DB::commit(); // Valider la transaction

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
        ->where('etat', 0)
        ->doesntExist();

    $session = SessionActivite::find($activite->sessions_id);

    \Log::info("Activité ID: $activite_id, Toutes tâches terminées: " . ($toutesTachesTerminees ? 'Oui' : 'Non'));
    \Log::info("Session état: " . ($session ? $session->etat : 'N/A'));
    \Log::info("Reconduir: " . ($activite->reconduir ?? 'N/A'));

    if ($session && $session->etat == 'terminer' && is_null($activite->reconduir)) {
        $nouvelEtat = 'inachever';
    } elseif ($toutesTachesTerminees) {
        $nouvelEtat = 'terminer';
         // Récupérer tous les utilisateurs dont le rôle est 'Administrateur' ou 'Invité'
        $utilisateurs = User::whereIn('role', ['Administrateur', 'Ordonateur','Chef-de-service'])->get();

        // Créer une notification pour chaque utilisateur
        foreach ($utilisateurs as $user) {
            NotificationActivite::create([
                'message'     => "L'activité '{$activite->libelle}' a été terminée.",
                'lu'          => 0,  // Notification non lue par défaut
                'user_id'     => $user->id,
                'activite_id' => $activite->id,
            ]);
        }
    } else {
        $nouvelEtat = 'en-cours';
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
        $taches = Tache::where('activite_id', $activiteId)->get();
        return response()->json($taches);
    }
}
