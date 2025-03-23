<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\SessionActivite;
use Illuminate\Http\Request;
use App\Models\Activite;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;
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
        'date_debut' => 'required|date',
        'date_fin' => 'required|date',
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
            $toutesTerminees = !Activite::where('etat_activite', '!=', 'terminer')->exists();
            if (!$toutesTerminees) {
                // Met à jour toutes les sessions "Ouvert" en "clôturé", sauf celle Ouvert d'édition
                SessionActivite::where('etat', 'Ouvert')->update(['etat' => 'En_Cours']);
            }
        } else {
            SessionActivite::where('etat', 'Ouvert')->update(['etat' => 'Clôturé']);
        }

        $session = SessionActivite::create([
            'annee' => $request->annee,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'etat' => $request->etat,
            'plan_strategiques_id' => $request->plan_strategiques_id, 
        ]);
        $users = User::whereNotIn('role', ['Administrateur'])->get();
    // Créer une notification pour chaque utilisateur
    foreach ($users as $user) {
        $article='Le';
        if($user->role=='Ordonateur'){
            $article="L'";
        }
        $messageContent = "Bonjour Monsieur/Madame {$article} {$user->role}, 
        la session de proposition des projets d'activité pour l'année {$request->annee} 
        débute le {$request->date_debut} et prend fin le {$request->date_fin}.";

        EmailService::sendEmail($user->email, $messageContent);
    }
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
            'annee' => 'required|digits:4',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'etat' => 'required|in:Ouvert,Clôturé,En_Cours',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        // Vérifier si l'état est "Ouvert" et gérer les sessions existantes
        if ($request->etat === 'Ouvert') {
            $sessionsOuvertes = SessionActivite::where('etat', 'Ouvert')
                ->where('id', '!=', $session->id)
                ->get();
            foreach ($sessionsOuvertes as $sessionsOuverte) {
                $etatActivite = Activite::where('etat_activite', '!=', 'terminer')->exists();
                if ($etatActivite) {
                    $sessionsOuverte->update(['etat' => 'En_Cours']);
                } else {
                    $sessionsOuverte->update(['etat' => 'Clôturé']);
                }
                # code...
            }
            
        }
    
        // Mise à jour de la session avec les nouvelles données
        $session->update([
            'annee' => $request->annee,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'etat' => $request->etat,
        ]);
    
        // Notification aux utilisateurs
        $users = User::whereNotIn('role', ['Administrateur'])->get();
        foreach ($users as $user) {
            $article = ($user->role == 'Ordonateur') ? "L'" : "Le";
            $messageContent = "Bonjour Monsieur/Madame {$article} {$user->role},\n\n"
                . "La fin de la session de proposition des projets d'activité pour l'année {$request->annee} "
                . "a été repoussée au {$request->date_fin}.";
    
            // Envoi d'e-mails en tâche de fond
            EmailService::sendEmail($user->email, $messageContent);
        }
    
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
    public function supprimerSession($id)
{
    // Vérifier si la session est associée à une activité
    $sessionAssociee = DB::table('activites')->where('sessions_id', $id)->exists();

    if ($sessionAssociee) {
        // Si la session est associée à une activité, on renvoie un message d'erreur
        return response()->json(['message' => 'La session est associée à une activité elle ne peu pas etre supprime.'], 200);
    } else {
        // Sinon, on la supprime
        DB::table('sessions_activites')->where('id', $id)->delete();
        return response()->json(['message' => 'La session a été supprimée avec succès.'], 200);
    }
}

}
