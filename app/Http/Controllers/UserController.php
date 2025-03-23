<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\NotificationActivite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;
class UserController extends Controller
{
    /**
     * Afficher tous les Users avec leurs structures.
     */
    public function index()
    {
        try {
            $users = User::where('email', '!=', 'gpaujkz@gmail.com')->with('structure:id,sigle')
                ->orderBy('id', 'desc')
                ->get();
            return response()->json($users);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des Users : {$e->getMessage()}");
            return response()->json([
                'message' => 'Erreur lors de la récupération des Users.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer l'utilisateur connecté.
     */
    public function getUserInfo()
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non connecté'], 401);
        }
        // Récupérer les notifications non lues
        $notifications = NotificationActivite::where('user_id', $user->id)
            ->where('lu', 0)
            ->get();
        return response()->json([
            'role' => $user->role,
            'email' => $user->email,
            'structure' => $user->structure ? [
            'id' => $user->structure->id,
            'etat' => $user->etat,
            'sigle' => $user->structure->sigle,
            ] : null,
            'notifications' => $notifications,
            'notificationsCount' => $notifications->count()
        ]);
    }
    /**
     * Créer un nouvel User avec un mot de passe par défaut.
     */
    public function store(Request $request)
    {
        Log::info('Données reçues pour l\'User:', $request->all());
        // Validation des données
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email|regex:/^[a-zA-Z0-9._%+-]+@ujkz\.bf$/',
            'role' => 'required|string|max:50',
            'etat' => 'required|string|max:50',
            'structure_id' => 'required|exists:structures,id',
        ], [
            'email.unique' => 'Cet email est déjà utilisé.',
            'email.regex' => 'L\'adresse email doit se terminer par @ujkz.bf.',
            'structure_id.exists' => 'La structure sélectionnée n\'existe pas.'
        ]);
        
        // Mot de passe par défaut sécurisé
        $mois = Carbon::now()->format('m'); // Mois actuel
        $annee = Carbon::now()->format('Y'); // Année actuelle
        // Générer un mot de passe aléatoire
        $motDePasse = 'Gpaujkz'.$mois.$annee;
        try {
            DB::transaction(function () use ($validatedData, &$user, $motDePasse) {
                // Créer l'User
                $user = User::create([
                    'email' => $validatedData['email'],
                    'password' => Hash::make($motDePasse),
                    'role' => $validatedData['role'],
                    'etat' => $validatedData['etat'],
                    'structure_id' => $validatedData['structure_id'],
                ]);
            });
            Log::info('User créé:', $user->toArray());
            // Envoi d'un e-mail à l'utilisateur
            $article='Le';
            if($user->role=='Administrateur'||$user->role=='Ordonateur'){
                $article="L'";
            }
            $messageContent = "Bonjour Monsieur/Madame $article {$user->role}, bienvenue sur notre plateforme ! votre mot de passe par défaut est : $motDePasse que vous allez change à la première connexion.";
            EmailService::sendEmail($user->email, $messageContent);

            return response()->json([
                'message' => 'User créé avec succès',
                'User' => $user,
                'mot_de_passe' => $motDePasse, // À supprimer de la réponse en production
            ], 201);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la création de l'User : {$e->getMessage()}");
            return response()->json([
                'message' => 'Erreur lors de la création de l\'utilisateur.',
                'error' => $e->getMessage()
            ], 500);
            
        }
    }
    /**
     * Mettre à jour les informations d'un User.
     */
    public function update(Request $request, $id)
{
    try {
        $user = User::findOrFail($id);

        // Validation des données
        $validatedData = $request->validate([              
            'email' => 'sometimes|email|unique:users,email,' . $id . '|regex:/^[a-zA-Z0-9._%+-]+@ujkz\.bf$/',
            'role' => 'sometimes|string|max:50',
            'etat' => 'sometimes|string|max:50', 
            'structure_id' => 'sometimes|exists:structures,id',
            'password' => 'sometimes|string|min:8|confirmed',
        ], [
            'email.regex' => 'L\'adresse email doit obligatoirement se terminer par @ujkz.bf.',
        ]);
        
        // Vérifier et hacher le mot de passe si présent
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        // Mettre à jour l'utilisateur
        $user->update($validatedData);

        return response()->json([
            'message' => 'User mis à jour avec succès',
            'User' => $user,
        ]);
    } catch (\Exception $e) {
        Log::error("Erreur lors de la mise à jour de l'User : {$e->getMessage()}");
        return response()->json([
            'message' => 'Erreur lors de la mise à jour de l\'User.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Réinitialiser le mot de passe d'un User.
     */
    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);

            // Nouveau mot de passe par défaut
            $mois = Carbon::now()->format('m'); // Mois actuel
            $annee = Carbon::now()->format('Y'); // Année actuelle
            // Générer un mot de passe aléatoire
            $motDePasse = 'Gpaujkz'.$mois.$annee;
            $user->password = Hash::make($motDePasse);
            $user->save();
            $article='Le';
            if($user->role=='Administrateur'||$user->role=='Ordonateur'){
                $article="L'";
            }
            $messageContent = "Bonjour Monsieur/Madame $article {$user->role}, votre mot de passe a ete restorer avec sucess.  Utilise le mot de passe par défaut est : $motDePasse que vous allez change à la première connexion. ";
            EmailService::sendEmail($user->email, $messageContent);
            return response()->json([
                'message' => 'Mot de passe réinitialisé avec succès.',
                'nouveau_mot_de_passe' => $motDePasse, // À masquer en production
            ], 200);
            // Envoi d'un e-mail à l'utilisateur
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de la réinitialisation du mot de passe : {$e->getMessage()}");
            return response()->json([
                'message' => 'Erreur lors de la réinitialisation du mot de passe.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function supprimerUtilisateur($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'Utilisateur non trouvé'], 404);
    }

    // Vérifier si l'utilisateur est associé à une activité
    $hasActivities = Activite::where('user_id', $id)->exists();

    if ($hasActivities) {
        // Masquer l'utilisateur sans suppression
        $user->masque = 1;
        $user->save();

        return response()->json(['message' => 'Utilisateur masqué car il est associé à des activités.']);
    } else {
        // Supprimer définitivement l'utilisateur
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
    }
}

    /**
     * Compter le nombre d'utilisateurs.
     */
    public function countUsers()
    {
        $userCount = User::count();
        $userCount = ($userCount -1);
        return response()->json(['count' => $userCount], 200);
    }

}
