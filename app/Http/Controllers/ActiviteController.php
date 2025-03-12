<?php

namespace App\Http\Controllers;
use App\Models\User;
use Carbon\Carbon;
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
    public function recomduireActivite(Request $request, $id)
    {
        try {
            $activite = Activite::findOrFail($id);
    
            if ($activite->etat_activite !== 'en-attente') {
                return response()->json([
                    'message' => 'Seules les activités en attente peuvent être reconduites.',
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
            $users = User::where('id', '=', $activite->user_id)->with('structure:id,sigle')
            ->first();

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
        } catch (\Exception $e) {
            Log::error('Erreur lors de la reconduction', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erreur lors de la mise à jour.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function ActiviteDetailles($id)
{
    $activite = Activite::with(['indicateurs', 'structure', 'objectifStrategique', 'effetsAttendus'])->findOrFail($id);

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
        // Rechercher les activités en fonction de l'utilisateur, de sa structure et de la session en cours
        $activites = Activite::where('structure_id', $user->structure_id)
            ->where('sessions_id', $sessionEnCours->id)
            ->where('hort_progamme', 0)
            ->orWhere('reconduir',  $sessionEnCours->annee)
            ->where('structure_id', $user->structure_id)
    
            ->get();
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
    ->with('structure') // Charger la relation 'structure'
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
            'structure_sigle' => $activite->structure ? $activite->structure->sigle : null,
            'etat_session' => $session->etat, // Ajouter l'état de la session
            'reconduir'=>$activite->reconduir,
            'etat_slection'=>$activite->etat_slection,
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
    ->with('structure') // Charger la relation 'structure'
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
            'structure_sigle' => $activite->structure ? $activite->structure->sigle : null,
            'etat_session' => $session->etat, // Ajouter l'état de la session
            'reconduir'=>$activite->reconduir,
            'etat_slection'=>$activite->etat_slection,
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
            ->orWhere('reconduir', $session->annee); // Condition pour reconduir égal à l'année de la session
    })
    ->where('etat_slection', 'Validé') // Filtrer uniquement les activités soumises
    ->with('structure') // Charger la relation 'structure'
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
            'structure_sigle' => $activite->structure ? $activite->structure->sigle : null,
            'etat_session' => $session->etat, // Ajouter l'état de la session
            'reconduir'=>$activite->reconduir,
            'etat_slection'=>$activite->etat_slection,
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

    // Rechercher uniquement les activités validées de la structure OU celles reconduites pour l'année de la session
    $activites = Activite::where(function ($query) use ($session, $user) {
        $query->where('sessions_id', $session->id)
              ->where('structure_id', $user->structure_id)
              ->where('etat_slection', 'Validé'); // Activités validées de la structure

        $query->orWhere(function ($subQuery) use ($session, $user) {
            $subQuery->where('reconduir', $session->annee)
                     ->where('structure_id', $user->structure_id)
                     ->where('etat_slection', 'Validé'); // Activités reconduites validées
        });
    })
    ->with('structure')
    ->get();

    Log::info('Activités récupérées.', ['nombre_activites' => $activites->count()]);

    $activitesWithStructureSigle = $activites->map(function ($activite) use ($session) {
        return [
            'id' => $activite->id,
            'libelle' => $activite->libelle,
            'etat_activite' => $activite->etat_activite,
            'sessions_id' => $activite->sessions_id,
            'structure_sigle' => optional($activite->structure)->sigle,
            'etat_session' => $session->etat,
            'reconduir' => $activite->reconduir,
            'etat_slection' => $activite->etat_slection,
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
            'formactivite.structure_id'=> 'required|exists:structures,id',
            'formactivite.etat'=> 'required|string|max:255',
            'formactivite.libelle' => 'required|string|max:255',
            'formactivite.finance_etat' => 'nullable|numeric',
            'formactivite.partenaire' => 'nullable|string|max:255',
            'formactivite.hort_progamme' => 'nullable|boolean',
            'formactivite.finance_partenaire' => 'nullable|numeric',
            'formactivite.trimestre_1' => 'nullable|boolean',
            'formactivite.trimestre_2' => 'nullable|boolean',
            'formactivite.trimestre_3' => 'nullable|boolean',
            'formactivite.trimestre_4' => 'nullable|boolean',
            'Indicateur' => 'required|array|min:1',
            'Indicateur.*.indicateur' => 'required|string|max:255',
            'Indicateur.*.unite' => 'required|string|max:50',
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
        \Log::info('Récupération de l\'utilisateur connecté.');
        $user = Auth::user();

        \Log::info('Vérification de la session d\'activité en cours.');
        $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();
        if (!$sessionEnCours) {
            \Log::warning('Aucune session en cours trouvée.');
            return response()->json(['message' => 'Aucune session en cours trouvée.'], 400);
        }

        \Log::info('Création de l\'activité.');
        $activite = Activite::create([
            'user_id' => $user->id,
            'structure_id' => $validated['formactivite']['structure_id'],
            'objectif_strategique_id' => $validated['formactivite']['objectif_strategique_id'],
            'effets_attendus_id' => $validated['formactivite']['effets_attendus_id'],
            'libelle' => $validated['formactivite']['libelle'],
            'finance_etat' => $validated['formactivite']['finance_etat'] ?? null,
            'partenaire' => $validated['formactivite']['partenaire'] ?? null,
            'hort_progamme'=> $validated['formactivite']['hort_progamme'] ?? null,
            'finance_partenaire' => $validated['formactivite']['finance_partenaire'] ?? null,
            'trimestre_1' => $validated['formactivite']['trimestre_1'] ?? 0,
            'trimestre_2' => $validated['formactivite']['trimestre_2'] ?? 0,
            'trimestre_3' => $validated['formactivite']['trimestre_3'] ?? 0,
            'trimestre_4' => $validated['formactivite']['trimestre_4'] ?? 0,
            'sessions_id' => $sessionEnCours->id,
        ]);
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
        $activite = Activite::find($id);

        if (!$activite) {
            return response()->json(['message' => 'Activité non trouvée.'], 404);
        }

        $activite->etat_slection = $request->input('etat_slection');
        $activite->save();
        if($activite->etat_slection== 'Validé'){
            $user = Auth::user();
            $messageContent = "Bonjour Monsieur/Madame {$user->nom}, votre activité  $activite->libelle été validé .";

            EmailService::sendEmail($user->email, $messageContent);
            // Créer une notification pour chaque utilisateur
                NotificationActivite::create([
                    'message'     => "L'activité '{$activite->libelle}' a été valider.",
                    'lu'          => 0,  // Notification non lue par défaut
                    'user_id'     => $activite->user_id,
                    'activite_id' => $activite->id,
                ]);
            
        }
        return response()->json($activite);
    }
    public function update(Request $request, $id)
{
    \Log::info('Début de la méthode update.');

    // Validation des données d'entrée
    try {
        \Log::info('Début de la validation des données.');
        $validated = $request->validate([
            'formactivite.objectif_strategique_id' => 'required|exists:objectifs_strategiques,id',
            'formactivite.effets_attendus_id' => 'required|exists:effets_attendus,id',
            'formactivite.structure_id' => 'required|exists:structures,id',
            'formactivite.etat' => 'required|string|max:255',
            'formactivite.libelle' => 'required|string|max:255',
            'formactivite.finance_etat' => 'nullable|numeric',
            'formactivite.partenaire' => 'nullable|string|max:255',
            'formactivite.finance_partenaire' => 'nullable|numeric',
            'formactivite.trimestre_1' => 'nullable|boolean',
            'formactivite.trimestre_2' => 'nullable|boolean',
            'formactivite.trimestre_3' => 'nullable|boolean',
            'formactivite.trimestre_4' => 'nullable|boolean',
            'Indicateur' => 'required|array|min:1',
            'Indicateur.*.indicateur' => 'required|string|max:255',
            'Indicateur.*.unite' => 'required|string|max:50',
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

        \Log::info('Mise à jour des données de l\'activité.');
        $activite->update([
            'structure_id' => $validated['formactivite']['structure_id'],
            'objectif_strategique_id' => $validated['formactivite']['objectif_strategique_id'],
            'effets_attendus_id' => $validated['formactivite']['effets_attendus_id'],
            'libelle' => $validated['formactivite']['libelle'],
            'finance_etat' => $validated['formactivite']['finance_etat'] ?? null,
            'partenaire' => $validated['formactivite']['partenaire'] ?? null,
            'finance_partenaire' => $validated['formactivite']['finance_partenaire'] ?? null,
            'trimestre_1' => $validated['formactivite']['trimestre_1'] ?? 0,
            'trimestre_2' => $validated['formactivite']['trimestre_2'] ?? 0,
            'trimestre_3' => $validated['formactivite']['trimestre_3'] ?? 0,
            'trimestre_4' => $validated['formactivite']['trimestre_4'] ?? 0,
        ]);

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

public function getActivitesByEffetAttendu($effetAttenduId)
{
    // Récupérer la session en cours
    $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();

    // Vérifier si une session en cours existe
    if (!$sessionEnCours) {
        return response()->json(['message' => 'Aucune session en cours trouvée'], 404);
    }

    // Obtenir l'année de la session en cours
    $anneeSessionEnCours = $sessionEnCours->annee;

    // Récupérer les activités de la session en cours ou celles reconduites à l'année de la session en cours
    $activites = Activite::where('effets_attendus_id', $effetAttenduId)
        ->where(function ($query) use ($anneeSessionEnCours, $sessionEnCours) {
            $query->where('sessions_id', $sessionEnCours->id) // Activités de la session en cours
                  ->where('etat_slection', 'Validé')           // Filtrées par etat_slection "Validé"
                  ->orWhere('reconduir', $anneeSessionEnCours); // Activités reconduites à l'année de la session
        })
        ->with(['taches', 'indicateurs', 'structure', 'session',]) // Charger les relations nécessaires
        ->get();

    // Vérifier si des activités ont été trouvées
    if ($activites->isEmpty()) {
        return response()->json(['message' => 'Aucune activité trouvée pour cet effet attendu'], 200);
    }

    // Retourner les activités trouvées
    return response()->json($activites);
}

public function getActivitesByEffetAttenduStructure($effetAttenduId)
{
    // Récupérer la session en cours
    $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();

    // Vérifier si une session en cours existe
    if (!$sessionEnCours) {
        return response()->json(['message' => 'Aucune session en cours trouvée'], 404);
    }
    $user = Auth::user();
    // Obtenir l'année de la session en cours
    $anneeSessionEnCours = $sessionEnCours->annee;

    // Récupérer les activités de la session en cours ou celles reconduites à l'année de la session en cours
    $activites = Activite::where('effets_attendus_id', $effetAttenduId)
        ->where(function ($query) use ($anneeSessionEnCours, $sessionEnCours) {
            $query->where('sessions_id', $sessionEnCours->id) // Activités de la session en cours
                  ->where('etat_slection', 'Validé')  // Filtrées par etat_slection "Validé"
                  ->where('structure_id', $user->structure_id)         
                  ->orWhere('reconduir', $anneeSessionEnCours); // Activités reconduites à l'année de la session
        })
        ->with(['taches', 'indicateurs', 'structure', 'session',]) // Charger les relations nécessaires
        ->get();

    // Vérifier si des activités ont été trouvées
    if ($activites->isEmpty()) {
        return response()->json(['message' => 'Aucune activité trouvée pour cet effet attendu'], 200);
    }

    // Retourner les activités trouvées
    return response()->json($activites);
}
public function getActivitesByEffetAttenduTrimestre($effetAttenduId)
{
    // Récupérer la session en cours
    $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();

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
                  ->orWhere('reconduir', $anneeSessionEnCours); // Activités reconduites à l'année de la session
        })
        
        ->with(['taches', 'indicateurs', 'structure', 'session',]) // Charger les relations nécessaires
        ->get();

    // Vérifier si des activités ont été trouvées
    if ($activites->isEmpty()) {
        return response()->json(['message' => 'Aucune activité avec un rapport trouvée pour cet effet attendu'], 200);
    }

    // Retourner les activités trouvées
    return response()->json($activites);
}



}
