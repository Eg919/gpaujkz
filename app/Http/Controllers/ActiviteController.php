<?php

namespace App\Http\Controllers;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Tache;
use App\Models\Activite;
use App\Models\Indicateur;
use App\Models\SessionActivite;
use Illuminate\Http\Request;
use App\Models\NotificationActivite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class ActiviteController extends Controller
{
    /**
     * Vérifie si la session associée à une activité est clôturée.
     */
    private function verifySessionNotClosed($activiteId)
    {
        $activite = Activite::find($activiteId);
        if (!$activite) return;

        $session = SessionActivite::find($activite->sessions_id);
        if ($session && $session->etat === 'Clôturé') {
            abort(403, "Cette session est clôturée. Toute modification est interdite.");
        }
        
        $user = Auth::user();
        if ($user && !in_array($user->role, ['Administrateur']) && $activite->structure_id !== $user->structure_id) {
            abort(403, "Accès non autorisé : cette activité n'appartient pas à votre structure.");
        }
    }

    public function recomduireActivite(Request $request, $id)
    {
        try {
            $this->verifySessionNotClosed($id);
            $activite = Activite::findOrFail($id);
    
            if ($activite->etat_activite !== 'en-attente'||$activite->etat_activite !== 'en-cours') {
                return response()->json([
                    'message' => 'Seules les activités en attente ou en cours peuvent être reconduites.',
                ], 400);
            }
    
            $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->latest('created_at')->first();
    
            if (!$sessionEnCours) {
                return response()->json([
                    'message' => 'Aucune session en cours trouvée.',
                ], 404);
            }
    
            $activite->reconduir = $sessionEnCours->annee;
            $activite->save();

            NotificationActivite::create([
                'message'     => "L'activité '{$activite->libelle}' a été reconduit.",
                'lu'          => 0,  // Notification non lue par défaut
                'user_id'     => $activite->user_id,
                'activite_id' => $activite->id,
            ]);
            return response()->json([
                'message' => 'Reconduire mis à jour avec succès.',
                'activite' => $activite,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('Activité introuvable', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Activité introuvable.',
            ], 404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la reconduction', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erreur lors de la mise à jour.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function mettreAJourEtatActivite(Request $request, $id)
{
    $request->validate([
        'etat_activite' => 'required|string|max:50',
    ]);

    try {
        $this->verifySessionNotClosed($id);
        $activite = Activite::findOrFail($id);
        $activite->etat_activite = $request->etat_activite;
        $activite->save();

        return response()->json([
            'message' => 'État de l\'activité mis à jour avec succès.',
            'etat_activite' => $activite->etat_activite,
        ]);
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        throw $e;
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 403);
    }
}

public function ActiviteDetailles($id)
{
    $activite = Activite::with(['indicateurs', 'structure', 'objectifStrategique', 'effetsAttendus', 'structuresPartenaires'])->findOrFail($id);

    return response()->json([
        'activite' => $activite,
        'indicateurs' => $activite->indicateurs,
        'structure' => $activite->structure,
        'objectifStrategique' => $activite->objectifStrategique,
        'effet_attendus' => $activite->effetsAttendus,
    ]);
}

    
public function mettreAJourObservation(Request $request, $id){
    $request->validate([
        'observation' => 'required',
    ]);
    try {
        $this->verifySessionNotClosed($id);
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        throw $e;
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 403);
    }
    $activite = Activite::findOrFail($id);
    $activite->observation = $request->observation;
    $activite->save();
    return response()->json([
        'message' => 'Observation mise à jour avec succès.',
        'observation' => $activite->observation,
    ]);
}
    
public function mettreAJourEtatFinancier(Request $request, $id)
{
    // Validation des données d'entrée
    $request->validate([
        'etat_financier' => 'required|numeric|min:0',
    ]);

    // Récupérer l'activité ou échouer
    $activite = Activite::findOrFail($id);
    try {
        $this->verifySessionNotClosed($id);
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        throw $e;
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 403);
    }

    
    DB::beginTransaction(); // Début de transaction

    try {
        // Mise à jour de l'état financier de l'activité
        $activite->etat_financier = $request->etat_financier;
        $activite->save();

    
        // Récupérer le trimestre actuel
        $now = Carbon::now();
        $trimestre = ceil($now->month / 3);

        // Mettre à jour le coût du trimestre correspondant
        if ($trimestre == 1) {
            $activite->coute_t1 = $activite->etat_financier;
        } elseif ($trimestre == 2) {
            $activite->coute_t2 = $activite->etat_financier;
        } elseif ($trimestre == 3) {
            $activite->coute_t3 = $activite->etat_financier;
        } elseif ($trimestre == 4) {
            $activite->coute_t4 = $activite->etat_financier;
        }

        $activite->save();

        DB::commit(); // Valider la transaction

        // Retourner une réponse JSON
        return response()->json([
            'message' => 'État financier mis à jour avec succès.',
            'etat_financier' => $activite->etat_financier,
        ]);

    } catch (\Exception $e) {
        DB::rollBack(); // Annuler la transaction en cas d'erreur
        return response()->json([
            'message' => 'Une erreur est survenue lors de la mise à jour.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function soumettreActivite(Request $request, $id)
{
    try {
        // Valider les données reçues
        $validated = $request->validate([
            'Soumi' => 'required|boolean', // Assurez-vous que Soumi est un booléen
        ]);

        // Récupérer l'activité correspondante
        $activite = Activite::find($id);
        if (!$activite) {
            return response()->json([
                'message' => 'L\'activité n\'a pas été trouvée.',
            ], 404);
        }

        try {
            $this->verifySessionNotClosed($id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        // Vérifier que l'activité a au moins une tâche planifiée avant soumission
        if ($validated['Soumi'] == 1) {
            $nbTaches = Tache::where('activite_id', $activite->id)->count();
            if ($nbTaches === 0) {
                return response()->json([
                    'message' => 'Vous devez planifier au moins une tâche avant de soumettre l\'activité.',
                ], 422);
            }

            // Vérifier que le total des pourcentages des tâches = 100%
            /* 
            $totalPourcentage = Tache::where('activite_id', $activite->id)->sum('pourcentage_tache');
            if ($totalPourcentage != 100) {
                return response()->json([
                    'message' => 'Le total des pourcentages des tâches doit être égal à 100% avant soumission. Actuellement : ' . $totalPourcentage . '%.',
                ], 422);
            }
            */
        }

        // Mettre à jour l'état de soumission
        $activite->soumi = $validated['Soumi'];
        $activite->save();
        $utilisateurs = User::whereIn('role', ['Administrateur','Chef-de-service'])->get();

    // Créer une notification pour chaque utilisateur
    foreach ($utilisateurs as $user) {
        NotificationActivite::create([
            'message'     => "L'activité '{$activite->libelle}' a été soumi.",
            'lu'          => 0,  // Notification non lue par défaut
            'user_id'     => $user->id,
            'activite_id' => $activite->id,
        ]);
    }

        // Retourner une réponse JSON de succès
        return response()->json([
            'message' => 'État de soumission mis à jour avec succès.',
            'activite' => $activite,
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Retourner une réponse d'erreur de validation
        return response()->json([
            'message' => 'Validation des données échouée.',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        // Gérer les autres erreurs (ex. : activité non trouvée)
        Log::error('Erreur lors de la mise à jour de la soumission', ['error' => $e->getMessage()]);
        return response()->json([
            'message' => 'Une erreur est survenue lors de la mise à jour.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    
    public function show($activiteId)
{
    \Log::info("Requête pour récupérer l'activité avec l'ID : $activiteId");

    try {
        $activite = Activite::with('indicateurs')->findOrFail($activiteId);
        return response()->json([
            'activite' => $activite,
            'indicateurs' => $activite->indicateurs,
        ], 200);
    } catch (ModelNotFoundException $e) {
        \Log::error("Activité introuvable pour l'ID : $activiteId");
        return response()->json(['message' => 'Activité non trouvée.'], 404);
    }
}

    /**
     * Compter les activités de l'utilisateur connecté pour la session en cours.
     */
    public function countUserActivities()
    {
        // Obtenir l'utilisateur connecté
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non connecté'], 401);
        }
        // Obtenir la session en cours
        $currentSession = SessionActivite::where('etat', 'Ouvert')->first();
        if (!$currentSession) {
            return response()->json(['count' => 0], 200);
        }
        // Compter les activités de l'utilisateur connecté pour la session en cours
        $activityCount = Activite::where('structure_id', $user->structure_id)
            ->where('sessions_id', $currentSession->id)
            ->count();
        return response()->json(['count' => $activityCount], 200);
    }
    /**
     * Compter le nombre d'activités pour la session en cours.
     */
    public function countActivitesEnCours()
    {
        // Trouver la session d'activité en cours
        $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();
        if (!$sessionEnCours) {
            return response()->json([
                'count' => 0,
                'message' => 'Aucune session d\'activité en cours trouvée.'
            ], 200);
        }
        // Compter les activités liées à cette session
        $activiteCount = Activite::where('sessions_id', $sessionEnCours->id)
    // ->orWhere('reconduir', $sessionEnCours->annee)
    ->count();

        return response()->json(['count' => $activiteCount], 200);
    }
 /**
     * Compter le nombre d'activités validées dans la session d'activité en cours.
     */
    public function countValidatedActivities()
    {
        // Récupérer la session en cours
        $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();

        if (!$sessionEnCours) {
            return response()->json(['count' => 0, 'message' => 'Aucune session en cours trouvée'], 200);
        }
        // Compter les activités validées dans cette session
        $count = Activite::where('sessions_id', $sessionEnCours->id)
            ->where('etat_slection', 'Validé')
            ->where('confirmation_presi', 1)
            ->orWhere('reconduir', $sessionEnCours->annee)
            ->count();
        return response()->json(['count' => $count], 200);
    }
    /**
     * Récupère les activités pour un utilisateur connecté, sa structure, et une session.
     */
    public function getActivitesByStructureAndSession()
    {
        // Obtenir l'utilisateur connecté
        $user = Auth::user();
        // Obtenir la session en cours (par exemple, où l'état est 'en cours')
        $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();
        if (!$sessionEnCours) {
            return response()->json(['message' => 'Aucune session en cours trouvée.'], 404);
        }
        // Rechercher les activités en fonction de l'utilisateur, de sa structure (ou partenaire) et de la session en cours
        $activites = Activite::where(function($q) use ($user) {
                $q->where('structure_id', $user->structure_id)
                  ->orWhereHas('structuresPartenaires', function($subQ) use ($user) {
                      $subQ->where('structure_id', $user->structure_id);
                  });
            })
            ->where(function($query) use ($sessionEnCours) {
                $query->where('sessions_id', $sessionEnCours->id)
                      ->orWhere('reconduir', $sessionEnCours->annee);
            })
            ->where('hort_progamme', 0)
            ->with(['session']) // Charger la session pour l'état
            ->withCount('taches')
            ->get();

        $activites = $activites->map(function ($activite) {
            $activite->etat_session = $activite->session->etat ?? 'N/A';
            return $activite;
        });

        return response()->json($activites);
    }

    public function getActivitesBySessionHortProgramme(Request $request){
        // Log de la réception de la requête avec l'ID de session
    Log::info('Requête reçue pour récupérer les activités par session.', ['request' => $request->all()]);

    // Récupérer l'ID de session depuis les paramètres de la route
    $sessionId = $request->route('id');

    // Vérifier si un ID de session est passé en paramètre
    if (!$sessionId) {
        Log::error('Aucun ID de session fourni.');
        return response()->json(['message' => 'Aucun ID de session fourni.'], 400);
    }

    // Vérifier si la session existe
    $session = SessionActivite::find($sessionId);

    if (!$session) {
        Log::error('La session spécifiée est introuvable.', ['session_id' => $sessionId]);
        return response()->json(['message' => 'La session spécifiée est introuvable.'], 404);
    }

    // Log de la session trouvée
    Log::info('Session trouvée.', ['session_id' => $sessionId, 'session' => $session]);

    // Rechercher uniquement les activités soumises en fonction des critères spécifiés
    $activites = Activite::where(function ($query) use ($session) {
        $query->where('sessions_id', $session->id)
            ->where('hort_progamme', 1); // Condition pour reconduir égal à l'année de la session
    })
    ->with(['structure', 'user.structure']) // Charger la relation 'structure' et 'user.structure'
    ->get();

    // Log du nombre d'activités récupérées
    Log::info('Activités récupérées.', ['nombre_activites' => $activites->count()]);

    // Transformer les résultats pour inclure le sigle de la structure et l'état de la session
    $activitesWithStructureSigle = $activites->map(function ($activite) use ($session) {
        return [
            'id' => $activite->id,
            'libelle' => $activite->libelle,
            'etat_activite' => $activite->etat_activite,
            'sessions_id' => $activite->sessions_id,
            'structure_sigle' => $activite->structure_sigle, // Utilise l'accésseur du créateur
            'etat_session' => $session->etat, // Ajouter l'état de la session
            'reconduir'=>$activite->reconduir,
            'etat_slection'=>$activite->etat_slection,
            'confirmation_presi'=>$activite->confirmation_presi,
            'soumi' => $activite->soumi,
            'trimestre_1' => $activite->trimestre_1,
            'trimestre_2' => $activite->trimestre_2,
            'trimestre_3' => $activite->trimestre_3,
            'trimestre_4' => $activite->trimestre_4,
            'taux_t1' => $activite->taux_t1,
            'taux_t2' => $activite->taux_t2,
            'taux_t3' => $activite->taux_t3,
            'taux_t4' => $activite->taux_t4,
            'coute_t1' => $activite->coute_t1,
            'coute_t2' => $activite->coute_t2,
            'coute_t3' => $activite->coute_t3,
            'coute_t4' => $activite->coute_t4,
            'observation' => $activite->observation,
        ];
    });

    // Log des activités transformées
    Log::info('Activités transformées avec sigle de la structure et état de la session.', ['activites' => $activitesWithStructureSigle]);

    // Retourner la réponse JSON avec les activités et l'état de la session
    return response()->json($activitesWithStructureSigle);
    }
    /**
     * Récupère les activités pour une session et inclut les sigles de structure.
     */
   

     public function getActivitesBySession(Request $request)
{
    // Log de la réception de la requête avec l'ID de session
    Log::info('Requête reçue pour récupérer les activités par session.', ['request' => $request->all()]);

    // Récupérer l'ID de session depuis les paramètres de la route
    $sessionId = $request->route('id');

    // Vérifier si un ID de session est passé en paramètre
    if (!$sessionId) {
        Log::error('Aucun ID de session fourni.');
        return response()->json(['message' => 'Aucun ID de session fourni.'], 400);
    }

    // Vérifier si la session existe
    $session = SessionActivite::find($sessionId);

    if (!$session) {
        Log::error('La session spécifiée est introuvable.', ['session_id' => $sessionId]);
        return response()->json(['message' => 'La session spécifiée est introuvable.'], 404);
    }

    // Log de la session trouvée
    Log::info('Session trouvée.', ['session_id' => $sessionId, 'session' => $session]);

    // Rechercher uniquement les activités soumises en fonction des critères spécifiés
    $activites = Activite::where(function ($query) use ($session) {
        $query->where('sessions_id', $session->id)
            ->orWhere('reconduir', $session->annee); // Condition pour reconduir égal à l'année de la session
    })
    ->where('soumi', 1) // Filtrer uniquement les activités soumises
    ->with(['structure', 'user.structure']) // Charger la relation 'structure' et 'user.structure'
    ->get();

    // Log du nombre d'activités récupérées
    Log::info('Activités récupérées.', ['nombre_activites' => $activites->count()]);

    // Transformer les résultats pour inclure le sigle de la structure et l'état de la session
    $activitesWithStructureSigle = $activites->map(function ($activite) use ($session) {
        return [
            'id' => $activite->id,
            'libelle' => $activite->libelle,
            'etat_activite' => $activite->etat_activite,
            'sessions_id' => $activite->sessions_id,
            'structure_sigle' => $activite->structure_sigle, // Utilise l'accésseur du créateur
            'etat_session' => $session->etat, // Ajouter l'état de la session
            'reconduir'=>$activite->reconduir,
            'etat_slection'=>$activite->etat_slection,
            'confirmation_presi'=>$activite->confirmation_presi,
            'soumi' => $activite->soumi,
            'trimestre_1' => $activite->trimestre_1,
            'trimestre_2' => $activite->trimestre_2,
            'trimestre_3' => $activite->trimestre_3,
            'trimestre_4' => $activite->trimestre_4,
            'taux_t1' => $activite->taux_t1,
            'taux_t2' => $activite->taux_t2,
            'taux_t3' => $activite->taux_t3,
            'taux_t4' => $activite->taux_t4,
            'coute_t1' => $activite->coute_t1,
            'coute_t2' => $activite->coute_t2,
            'coute_t3' => $activite->coute_t3,
            'coute_t4' => $activite->coute_t4,
            'observation' => $activite->observation,
        ];
    });

    // Log des activités transformées
    Log::info('Activités transformées avec sigle de la structure et état de la session.', ['activites' => $activitesWithStructureSigle]);

    // Retourner la réponse JSON avec les activités et l'état de la session
    return response()->json($activitesWithStructureSigle);
}
public function getActivitesBySessionPa(Request $request)
{
    // Log de la réception de la requête avec l'ID de session
    Log::info('Requête reçue pour récupérer les activités par session.', ['request' => $request->all()]);

    // Récupérer l'ID de session depuis les paramètres de la route
    $sessionId = $request->route('id');

    // Vérifier si un ID de session est passé en paramètre
    if (!$sessionId) {
        Log::error('Aucun ID de session fourni.');
        return response()->json(['message' => 'Aucun ID de session fourni.'], 400);
    }

    // Vérifier si la session existe
    $session = SessionActivite::find($sessionId);

    if (!$session) {
        Log::error('La session spécifiée est introuvable.', ['session_id' => $sessionId]);
        return response()->json(['message' => 'La session spécifiée est introuvable.'], 404);
    }

    // Log de la session trouvée
    Log::info('Session trouvée.', ['session_id' => $sessionId, 'session' => $session]);

    // Rechercher uniquement les activités soumises en fonction des critères spécifiés
    $activites = Activite::where(function ($query) use ($session) {
        $query->where('sessions_id', $session->id)
            ->orWhere('reconduir', $session->annee);
    })
    ->where('etat_slection', 'Validé')
    // On assouplit la confirmation pour l'historique si nécessaire, mais on garde la cohérence
    // ->where('confirmation_presi', 1) 
    ->with(['structure', 'user.structure']) // Charger la relation 'structure' et 'user.structure'
    ->get();

    // Log du nombre d'activités récupérées
    Log::info('Activités récupérées.', ['nombre_activites' => $activites->count()]);

    // Transformer les résultats pour inclure le sigle de la structure et l'état de la session
    $activitesWithStructureSigle = $activites->map(function ($activite) use ($session) {
        return [
            'id' => $activite->id,
            'libelle' => $activite->libelle,
            'etat_activite' => $activite->etat_activite,
            'sessions_id' => $activite->sessions_id,
            'structure_sigle' => $activite->structure_sigle, // Utilise l'accésseur du créateur
            'etat_session' => $session->etat, // Ajouter l'état de la session
            'reconduir'=>$activite->reconduir,
            'etat_slection'=>$activite->etat_slection,
            'confirmation_presi'=>$activite->confirmation_presi,
            'soumi' => $activite->soumi,
            'trimestre_1' => $activite->trimestre_1,
            'trimestre_2' => $activite->trimestre_2,
            'trimestre_3' => $activite->trimestre_3,
            'trimestre_4' => $activite->trimestre_4,
            'taux_t1' => $activite->taux_t1,
            'taux_t2' => $activite->taux_t2,
            'taux_t3' => $activite->taux_t3,
            'taux_t4' => $activite->taux_t4,
            'coute_t1' => $activite->coute_t1,
            'coute_t2' => $activite->coute_t2,
            'coute_t3' => $activite->coute_t3,
            'coute_t4' => $activite->coute_t4,
            'observation' => $activite->observation,
        ];
    });

    // Log des activités transformées
    Log::info('Activités transformées avec sigle de la structure et état de la session.', ['activites' => $activitesWithStructureSigle]);

    // Retourner la réponse JSON avec les activités et l'état de la session
    return response()->json($activitesWithStructureSigle);
}
public function getActivitesBySessionStructure(Request $request)
{
    Log::info('Requête reçue pour récupérer les activités par session.', ['request' => $request->all()]);

    $sessionId = $request->route('id');
    $user = Auth::user();

    if (!$sessionId) {
        Log::error('Aucun ID de session fourni.');
        return response()->json(['message' => 'Aucun ID de session fourni.'], 400);
    }

    $session = SessionActivite::find($sessionId);

    if (!$session) {
        Log::error('La session spécifiée est introuvable.', ['session_id' => $sessionId]);
        return response()->json(['message' => 'La session spécifiée est introuvable.'], 404);
    }

    Log::info('Session trouvée.', ['session_id' => $sessionId, 'session' => $session]);

    // Rechercher uniquement les activités validées de la structure (ou partenaire) OU celles reconduites pour l'année de la session
    $activites = Activite::where(function ($query) use ($session, $user) {
        $query->where('sessions_id', $session->id)
              ->where(function($q) use ($user) {
                  $q->where('structure_id', $user->structure_id)
                    ->orWhereHas('structuresPartenaires', function($subQ) use ($user) {
                        $subQ->where('structure_id', $user->structure_id);
                    });
              })
              ->where('etat_slection', 'Validé')// Activités validées de la structure
              ->where('confirmation_presi', 1);
        $query->orWhere(function ($subQuery) use ($session, $user) {
            $subQuery->where('reconduir', $session->annee)
                     ->where(function($q) use ($user) {
                         $q->where('structure_id', $user->structure_id)
                           ->orWhereHas('structuresPartenaires', function($subQ) use ($user) {
                               $subQ->where('structure_id', $user->structure_id);
                           });
                     })
                     ->where('etat_slection', 'Validé'); // Activités reconduites validées
        });
    })
    ->with(['structure', 'user.structure'])
    ->get();

    Log::info('Activités récupérées.', ['nombre_activites' => $activites->count()]);

    $activitesWithStructureSigle = $activites->map(function ($activite) use ($session) {
        return [
            'id' => $activite->id,
            'libelle' => $activite->libelle,
            'etat_activite' => $activite->etat_activite,
            'sessions_id' => $activite->sessions_id,
            'structure_sigle' => $activite->structure_sigle, // Utilise l'accesseur intelligent (créateur)
            'etat_session' => $session->etat,
            'reconduir' => $activite->reconduir,
            'etat_slection' => $activite->etat_slection,
            'confirmation_presi' => $activite->confirmation_presi,
            'soumi' => $activite->soumi,
            'trimestre_1' => $activite->trimestre_1,
            'trimestre_2' => $activite->trimestre_2,
            'trimestre_3' => $activite->trimestre_3,
            'trimestre_4' => $activite->trimestre_4,
            'taux_t1' => $activite->taux_t1,
            'taux_t2' => $activite->taux_t2,
            'taux_t3' => $activite->taux_t3,
            'taux_t4' => $activite->taux_t4,
            'coute_t1' => $activite->coute_t1,
            'coute_t2' => $activite->coute_t2,
            'coute_t3' => $activite->coute_t3,
            'coute_t4' => $activite->coute_t4,
            'observation' => $activite->observation,
        ];
    });

    Log::info('Activités transformées avec sigle de la structure et état de la session.', ['activites' => $activitesWithStructureSigle]);

    return response()->json($activitesWithStructureSigle);
}

    /**
     * Enregistre une activité avec ses indicateurs associés.
     */
    public function store(Request $request)
{
    \Log::info('Début de la méthode store');

    // Validation des données d'entrée
    try {
        \Log::info('Début de la validation des données.');
        $validated = $request->validate([
            'formactivite.objectif_strategique_id' => 'required|exists:objectifs_strategiques,id',
            'formactivite.effets_attendus_id' => 'required|exists:effets_attendus,id',
            'formactivite.etat'=> 'required|string|max:255',
            'formactivite.libelle' => 'required|string|max:255',
            'formactivite.finance_etat' => 'nullable|numeric|min:0',
            'formactivite.partenaire' => 'nullable|string|max:255',
            'formactivite.hort_progamme' => 'nullable|boolean',
            'formactivite.finance_partenaire' => 'nullable|numeric|min:0',
            'formactivite.structures_partenaires_ids' => 'nullable|array',
            'formactivite.structures_partenaires_ids.*' => 'exists:structures,id',
            'formactivite.partenaires_list' => 'nullable|array',
            'formactivite.partenaires_list.*.nom' => 'required_with:formactivite.partenaires_list|string|max:255',
            'formactivite.partenaires_list.*.montant' => 'nullable|numeric|min:0',
            'formactivite.trimestre_1' => 'required|boolean',
            'formactivite.trimestre_2' => 'required|boolean',
            'formactivite.trimestre_3' => 'required|boolean',
            'formactivite.trimestre_4' => 'required|boolean',
            'Indicateur' => 'required|array|min:1',
            'Indicateur.*.indicateur' => 'required|string|max:255',
            'Indicateur.*.unite' => 'required|string|max:255',
            'Indicateur.*.reference' => 'required|string|max:255',
            'Indicateur.*.cible' => 'required|string|max:255',
        ]);
        \Log::info('Validation des données réussie.');
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Erreur de validation des données.', ['erreurs' => $e->errors()]);
        return response()->json([
            'message' => 'Erreur de validation des données.',
            'errors' => $e->errors(),
        ], 422);
    }

    try {
        \Log::info('Récupération de l\'utilisateur connecté.');
        $user = Auth::user();

        \Log::info('Vérification de la session d\'activité en cours.');
        $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();
        if (!$sessionEnCours) {
            \Log::warning('Aucune session en cours trouvée.');
            return response()->json(['message' => 'Aucune session en cours trouvée.'], 400);
        }

        \Log::info('Création de l\'activité.');
        $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();
        $dateJour = Carbon::now();
        $dateJourSuivant = $dateJour->addDay(); // Ajoute 1 jour
        if ($sessionEnCours->date_fin < $dateJourSuivant && $validated['formactivite']['hort_progamme'] == 0) {    
            return response()->json(['message' => 'La date limite de soumission des activités est dépassée.'], 400);
        }
        $activite = Activite::create([
            'user_id' => $user->id,
            'structure_id' => $user->structure_id, // Toujours utiliser la structure du créateur
            'objectif_strategique_id' => $validated['formactivite']['objectif_strategique_id'],
            'effets_attendus_id' => $validated['formactivite']['effets_attendus_id'],
            'libelle' => $validated['formactivite']['libelle'],
            'finance_etat' => $validated['formactivite']['finance_etat'] ?? null,
            'partenaire' => $validated['formactivite']['partenaire'] ?? null,
            'hort_progamme'=> $validated['formactivite']['hort_progamme'] ?? null,
            'finance_partenaire' => $validated['formactivite']['finance_partenaire'] ?? null,
            'partenaires_list' => $validated['formactivite']['partenaires_list'] ?? null,
            'trimestre_1' => $validated['formactivite']['trimestre_1'] ?? 0,
            'trimestre_2' => $validated['formactivite']['trimestre_2'] ?? 0,
            'trimestre_3' => $validated['formactivite']['trimestre_3'] ?? 0,
            'trimestre_4' => $validated['formactivite']['trimestre_4'] ?? 0,
            'sessions_id' => $sessionEnCours->id,
        ]);
        
        if (!empty($validated['formactivite']['structures_partenaires_ids'])) {
            $activite->structuresPartenaires()->sync($validated['formactivite']['structures_partenaires_ids']);
        }
        
        \Log::info('Activité créée avec succès.', ['activite_id' => $activite->id]);

        \Log::info('Ajout des indicateurs associés.');
        $indicateursData = collect($validated['Indicateur'])->map(function ($indicateurData) use ($activite) {
            return array_merge($indicateurData, ['activite_id' => $activite->id]);
        });

        Indicateur::insert($indicateursData->toArray());
        \Log::info('Indicateurs ajoutés avec succès.');

        \Log::info('Chargement des relations et préparation de la réponse.');
        $activite->load('indicateurs');

        return response()->json([
            'message' => 'Activité et indicateurs enregistrés avec succès!',
            'activite' => $activite,
        ], 201);
    } catch (\Exception $e) {
        \Log::error('Une erreur est survenue lors de l\'enregistrement.', [
            'exception_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'message' => 'Une erreur est survenue lors de l\'enregistrement.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    /**
     * Met à jour l'état d'une activité.
     */
    public function updateEtatActiviteSelection(Request $request, $id)
    {
        try {
            $this->verifySessionNotClosed($id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        $activite = Activite::find($id);

        if (!$activite) {
            return response()->json(['message' => 'Activité non trouvée.'], 404);
        }

        $activite->etat_slection = $request->input('etat_slection');

        // Si l'activité est rejetée, enregistrer le motif
        if ($request->input('etat_slection') === 'Rejeté') {
            $request->validate([
                'motif_rejet' => 'required|string|max:500',
            ]);
            $activite->motif_rejet = $request->input('motif_rejet');
        } else {
            $activite->motif_rejet = null;
        }

        $activite->save();
        
        return response()->json($activite);
    }
    public function update(Request $request, $id)
{
    \Log::info('Début de la méthode update.');

    // Vérifier si l'activité a déjà été soumise
    $activite = Activite::findOrFail($id);
    $user = Auth::user();
    if ($activite->etat_slection === 'Validé' && $activite->confirmation_presi == 1 && !($user->role === 'Administrateur' || $user->role === 'Chef-de-service')) {
        return response()->json([
            'message' => 'Cette activité a déjà été validée et confirmée, elle ne peut plus être modifiée.',
        ], 403);
    }

    // Validation des données d'entrée
    try {
        \Log::info('Début de la validation des données.');
        $validated = $request->validate([
            'formactivite.objectif_strategique_id' => 'required|exists:objectifs_strategiques,id',
            'formactivite.effets_attendus_id' => 'required|exists:effets_attendus,id',
            'formactivite.etat' => 'required|string|max:255',
            'formactivite.libelle' => 'required|string|max:255',
            'formactivite.finance_etat' => 'nullable|numeric|min:0',
            'formactivite.partenaire' => 'nullable|string|max:255',
            'formactivite.finance_partenaire' => 'nullable|numeric|min:0',
            'formactivite.structures_partenaires_ids' => 'nullable|array',
            'formactivite.structures_partenaires_ids.*' => 'exists:structures,id',
            'formactivite.partenaires_list' => 'nullable|array',
            'formactivite.partenaires_list.*.nom' => 'required_with:formactivite.partenaires_list|string|max:255',
            'formactivite.partenaires_list.*.montant' => 'nullable|numeric|min:0',
            'formactivite.trimestre_1' => 'required|boolean',
            'formactivite.trimestre_2' => 'required|boolean',
            'formactivite.trimestre_3' => 'required|boolean',
            'formactivite.trimestre_4' => 'required|boolean',
            'Indicateur' => 'required|array|min:1',
            'Indicateur.*.indicateur' => 'required|string|max:255',
            'Indicateur.*.unite' => 'required|string|max:255',
            'Indicateur.*.reference' => 'required|string|min:0',
            'Indicateur.*.cible' => 'required|string|min:0',
        ]);
        \Log::info('Validation des données réussie.');
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Erreur de validation des données.', ['erreurs' => $e->errors()]);
        return response()->json([
            'message' => 'Erreur de validation des données.',
            'errors' => $e->errors(),
        ], 422);
    }

    try {
        \Log::info('Recherche de l\'activité à modifier.');
        $activite = Activite::findOrFail($id);
        $this->verifySessionNotClosed($id);

        \Log::info('Mise à jour des données de l\'activité.');
        $activite->update([
            'objectif_strategique_id' => $validated['formactivite']['objectif_strategique_id'],
            'effets_attendus_id' => $validated['formactivite']['effets_attendus_id'],
            'libelle' => $validated['formactivite']['libelle'],
            'finance_etat' => $validated['formactivite']['finance_etat'] ?? null,
            'partenaire' => $validated['formactivite']['partenaire'] ?? null,
            'finance_partenaire' => $validated['formactivite']['finance_partenaire'] ?? null,
            'partenaires_list' => $validated['formactivite']['partenaires_list'] ?? null,
            'trimestre_1' => $validated['formactivite']['trimestre_1'] ?? 0,
            'trimestre_2' => $validated['formactivite']['trimestre_2'] ?? 0,
            'trimestre_3' => $validated['formactivite']['trimestre_3'] ?? 0,
            'trimestre_4' => $validated['formactivite']['trimestre_4'] ?? 0,
        ]);

        \Log::info('Synchronisation des structures partenaires.');
        $activite->structuresPartenaires()->sync($validated['formactivite']['structures_partenaires_ids'] ?? []);

        \Log::info('Mise à jour des indicateurs associés.');
        $activite->indicateurs()->delete();

        $indicateursData = collect($validated['Indicateur'])->map(function ($indicateurData) use ($activite) {
            return array_merge($indicateurData, ['activite_id' => $activite->id]);
        });

        Indicateur::insert($indicateursData->toArray());
        \Log::info('Indicateurs mis à jour avec succès.');

        \Log::info('Chargement des relations et préparation de la réponse.');
        $activite->load('indicateurs');

        return response()->json([
            'message' => 'Activité et indicateurs mis à jour avec succès!',
            'activite' => $activite,
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Une erreur est survenue lors de la mise à jour.', [
            'exception_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'message' => 'Une erreur est survenue lors de la mise à jour.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function getActivitesByEffetAttendu(Request $request, $effetAttenduId)
{
    $sessionId = $request->query('session_id');
    
    if ($sessionId) {
        $session = SessionActivite::find($sessionId);
    } else {
        $session = SessionActivite::where('etat', 'Ouvert')->first();
    }

    if (!$session) {
        return response()->json([], 200);
    }

    $anneeSession = $session->annee;

    $activites = Activite::where('effets_attendus_id', $effetAttenduId)
        ->where(function ($query) use ($anneeSession, $session) {
            $query->where(function ($q) use ($session, $anneeSession) {
                $q->where('sessions_id', $session->id)
                  ->orWhere('reconduir', $anneeSession);
            })
            ->where('etat_slection', 'Validé')
            ->where('confirmation_presi', 1);
        })
        ->with(['taches', 'indicateurs', 'structure', 'user.structure', 'session', 'structuresPartenaires'])
        ->get();

    // Vérifier si des activités ont été trouvées
    if ($activites->isEmpty()) {
        return response()->json(['message' => 'Aucune activité trouvée pour cet effet attendu'], 200);
    }

    // Retourner les activités trouvées
    return response()->json($activites);
}

public function getActivitesByEffetAttenduStructure(Request $request, $effetAttenduId)
{
    $user = Auth::user();
    $sessionId = $request->query('session_id');
    
    if ($sessionId) {
        $session = SessionActivite::find($sessionId);
    } else {
        $session = SessionActivite::where('etat', 'Ouvert')->first();
    }

    if (!$session) {
        return response()->json([], 200);
    }

    $anneeSession = $session->annee;

    $activites = Activite::where('effets_attendus_id', $effetAttenduId)
        ->where(function ($query) use ($anneeSession, $session, $user) {
            $query->where(function ($q) use ($session, $anneeSession) {
                $q->where('sessions_id', $session->id)
                  ->orWhere('reconduir', $anneeSession);
            })
            ->where('structure_id', $user->structure_id)
            ->where('etat_slection', 'Validé')
            ->where('confirmation_presi', 1);
        })
        ->with(['taches', 'indicateurs', 'structure', 'user.structure', 'session', 'structuresPartenaires'])
        ->get();

    // Vérifier si des activités ont été trouvées
    if ($activites->isEmpty()) {
        return response()->json(['message' => 'Aucune activité trouvée pour cet effet attendu'], 200);
    }

    // Retourner les activités trouvées
    return response()->json($activites);
}
public function getActivitesByEffetAttenduTrimestre(Request $request, $effetAttenduId)
{
    $sessionId = $request->query('session_id');
    
    if ($sessionId) {
        $sessionEnCours = SessionActivite::find($sessionId);
    } else {
        $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();
    }

    // Vérifier si une session en cours existe
    if (!$sessionEnCours) {
        return response()->json(['message' => 'Aucune session en cours trouvée'], 404);
    }

    // Obtenir l'année de la session en cours
    $anneeSessionEnCours = $sessionEnCours->annee;

    // Récupérer les activités qui ont au moins un rapport
    $activites = Activite::where('effets_attendus_id', $effetAttenduId)
        ->where(function ($query) use ($anneeSessionEnCours, $sessionEnCours) {
            $query->where('sessions_id', $sessionEnCours->id) // Activités de la session en cours
                  ->where('etat_slection', 'Validé')           // Filtrées par etat_slection "Validé"
                  ->where('confirmation_presi', 1)
                  ->orWhere('reconduir', $anneeSessionEnCours); // Activités reconduites à l'année de la session
        })
        
        ->with(['taches', 'indicateurs', 'structure', 'user.structure', 'session', 'structuresPartenaires']) // Charger les relations nécessaires
        ->get();

    // Vérifier si des activités ont été trouvées
    if ($activites->isEmpty()) {
        return response()->json(['message' => 'Aucune activité avec un rapport trouvée pour cet effet attendu'], 200);
    }

    // Retourner les activités trouvées
    return response()->json($activites);
}

public function confirmationPresi(Request $request, $id)
{
    $activite = Activite::find($id);

    if (!$activite) {
        return response()->json(['message' => 'Activité non trouvée.'], 404);
    }

    // Vérifier si le champ est envoyé, sinon mettre 0 par défaut
    $activite->confirmation_presi = (int) $request->input('confirmation_presi', 1);

    if (!$activite->save()) {
        return response()->json(['message' => 'Erreur lors de la mise à jour de l\'activité.'], 500);
    }

    if ($activite->confirmation_presi == 1) {
        $user = User::find($activite->user_id);

        if ($user) {
            $article ='Le';
            if ($user->role =='Administrateur'|| $user->role =='Ordonnateur') {
                $article = 'La';
            }
            $messageContent = "Bonjour Monsieur/Madame $article {$user->role}, votre activité '{$activite->libelle}' a été validée.";

            try {
                EmailService::sendEmail($user->email, $messageContent);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erreur lors de l\'envoi de l\'email.', 'error' => $e->getMessage()], 500);
            }

            NotificationActivite::create([
                'message'     => "L'activité '{$activite->libelle}' a été validée.",
                'lu'          => 0,
                'user_id'     => $activite->user_id,
                'activite_id' => $activite->id,
            ]);
        }
    }

    return response()->json(['message' => 'Activité mise à jour avec succès.', 'activite' => $activite]);
}


//Recuperer les activités validées
public function getActivitesValidees()
{
    $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();
    
    if (!$sessionEnCours) {
        return response()->json(['message' => 'Aucune session en cours trouvée.'], 404);
    }

    $activites = Activite::where(function ($query) use ($sessionEnCours) {
            $query->where('sessions_id', $sessionEnCours->id)
                  ->where('etat_slection', 'Validé');
        })
        ->orWhere('reconduir', $sessionEnCours->annee)
        ->get();

    return response()->json($activites);
}

public function tousConfirmer()
{
    // Récupérer la session en cours
    $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();
    if (!$sessionEnCours) {
        return response()->json(['message' => 'Aucune session en cours trouvée.'], 404);
    }

    // Récupérer les activités validées de cette session
    $activites = Activite::where('sessions_id', $sessionEnCours->id)
        ->where('etat_slection', 'Validé')
        ->where('confirmation_presi', '=', null)
        ->get();

    if ($activites->isEmpty()) {
        return response()->json(['message' => 'Aucune activité validée trouvée.'], 404);
    }

    DB::beginTransaction(); // Démarrer une transaction

    try {
        foreach ($activites as $activite) {
            $activite->confirmation_presi = 1;
            $activite->save();

            if ($activite->user) {
                $messageContent = "Bonjour {$activite->user->nom}, votre activité '{$activite->libelle}' a été validée.";

                try {
                    EmailService::sendEmail($activite->user->email, $messageContent);
                } catch (\Exception $e) {
                    // Enregistrer l'erreur sans interrompre la boucle
                    \Log::error("Erreur d'envoi d'email pour {$activite->user->email}: " . $e->getMessage());
                }

                // Créer une notification pour chaque utilisateur
                NotificationActivite::create([
                    'message'     => "L'activité '{$activite->libelle}' a été validée.",
                    'lu'          => 0, // Notification non lue par défaut
                    'user_id'     => $activite->user_id,
                    'activite_id' => $activite->id,
                ]);
            }
        }

        DB::commit(); // Valider la transaction
    } catch (\Exception $e) {
        DB::rollBack(); // Annuler la transaction en cas d'erreur
        return response()->json(['message' => 'Une erreur est survenue lors de la confirmation des activités.', 'error' => $e->getMessage()], 500);
    }

    return response()->json(['message' => 'Toutes les activités ont été confirmées.', 'activites' => $activites]);
}

public function supprimerActivite($id)
{
    $activite = Activite::find($id);
    if (!$activite) {
        return response()->json(['message' => 'Activité non trouvée.'], 404);
    }
    
    $user = Auth::user();
    if ($user && $user->role === 'Chef-de-service' && $activite->structure_id !== $user->structure_id) {
        return response()->json(['message' => 'Accès non autorisé : cette activité n\'appartient pas à votre structure.'], 403);
    }

    $taches = Tache::where('activite_id', $id)->get();
// Vérifier si des tâches sont associées à l'activité
    if (!$taches->isEmpty() ) {
        return response()->json(['message' => 'Impossible de supprimer l\'activité car des tâches y sont associées.'], 400);
    }
// if ($activite->confirmation_presi == 1) {
//     return response()->json(['message' => 'Impossible de supprimer l\'activité car elle a été validée.'], 400);
// }
    $activite->delete();

    return response()->json(['message' => 'Activité supprimée avec succès.'], 200);
}
}