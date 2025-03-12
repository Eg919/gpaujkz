<?php

namespace App\Http\Controllers;

use App\Models\SessionActivite;
use Illuminate\Http\Request;
use App\Models\Activite;
use Illuminate\Support\Facades\Validator;

class SessionActiviteController extends Controller
{
    public function getSessionEnCours()
    {
        try {
            // Recherche d'une session avec l'état 'Ouvert'
            $session = SessionActivite::where('etat', 'Ouvert')->first();

            if ($session) {
                return response()->json($session, 200); // Retourner la session avec un statut 200
            }

            return response()->json(['message' => 'Aucune session en cours trouvée.'], 404); // Aucun enregistrement trouvé

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur dans la récupération de la session en cours: ' . $e->getMessage()], 500);
        }
    }

    // Méthode pour afficher toutes les sessions
    public function index()
{
    $sessions = SessionActivite::orderBy('annee', 'desc')->get(); // Récupérer toutes les sessions par ordre décroissant
    return response()->json($sessions, 200);
}

    public function store(Request $request)
    {
       // Affichez toutes les données pour vérifier la requête
    \Log::info('Requête de création :', $request->all());
    
    // Continuez avec la validation
    $validator = Validator::make($request->all(), [
        'annee' => 'required|digits:4', 
        'etat' => 'required|in:Ouvert,Clôturé,En_Cours',
        'plan_strategiques_id' => 'required|exists:plans_strategiques,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);
    }

        $maxAnnee = SessionActivite::max('annee');
    
        if ($request->annee <= $maxAnnee) {
            return response()->json([
                'status' => 'error',
                'message' => 'L\'année doit être supérieure à l\'année la plus élevée existante dans la base de données.',
            ], 400);
        }

        if ($request->etat === 'Ouvert') {
            SessionActivite::where('etat', 'Ouvert')->update(['etat' => 'Clôturé,En_Cours']);
        }

        $session = SessionActivite::create([
            'annee' => $request->annee,
            'etat' => $request->etat,
            'plan_strategiques_id' => $request->plan_strategiques_id, 
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $session,
            'message' => 'Session créée avec succès !',
        ], 201);
    }

    
    // Méthode pour modifier une session
    
    
    public function update(Request $request, $id)
    {
        // Récupération de la session par ID
        $session = SessionActivite::find($id);
    
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Session non trouvée.',
            ], 404);
        }
    
        // Validation des données
        $validator = Validator::make($request->all(), [
            'annee' => 'required|digits:4', // Assurez-vous que l'année est un nombre à 4 chiffres
            'etat' => 'required|in:Ouvert,Clôturé,En_Cours',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        // Vérifier si l'état demandé est "Ouvert"
        if ($request->etat === 'Ouvert') {
            // Met à jour toutes les sessions "Ouvert" en "terminer", sauf celle Ouvert d'édition
            SessionActivite::where('etat', 'Ouvert')
                ->where('id', '!=', $session->id)
                ->update(['etat' => 'Terminer']);
        }
    
        // Mise à jour de la session avec les nouvelles données
        $session->update([
            'annee' => $request->annee,
            'etat' => $request->etat,
        ]);
    
        return response()->json([
            'status' => 'success',
            'data' => $session,
            'message' => 'Session mise à jour avec succès !',
        ], 200);
    }
    /**
     * Compter le nombre de sessions d'activités.
     */
    public function countSessions()
    {
        $sessionCount = SessionActivite::count(); // Compte le nombre total de sessions d'activités
        return response()->json(['count' => $sessionCount], 200);
    }
    public function getSessionByActivite($activiteId)
    {
        // Récupérer l'activité avec sa session associée
        $activite = Activite::with('session')->find($activiteId);

        if ($activite && $activite->session) {
            return response()->json([
                'session' => $activite->session,
            ]);
        } else {
            return response()->json([
                'message' => 'Activité ou session non trouvée',
            ], 404);
        }
    }
}
