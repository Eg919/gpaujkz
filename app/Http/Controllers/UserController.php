<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\NotificationActivite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;
class UserController extends Controller
{
    private function verifyIsAdmin()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['Administrateur'])) {
            abort(403, "Accès non autorisé : vous n'avez pas les droits d'administration pour cette action.");
        }
    }
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
        Log::info('Création d\'un nouvel User');
        try {
            $this->verifyIsAdmin();
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
        // Validation des données
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email|regex:/^[a-zA-Z0-9._%+-]+@ujkz\.bf$/',
            'role' => 'required|string|in:Administrateur,Chef-de-service,Responsable-de-structure,Point-Focale,Ordonnateur,Administrateur_DSI,Planificateur',
            'etat' => 'required|string|in:Actif,Inactif',
            'structure_id' => 'required|exists:structures,id',
        ], [
            'email.unique' => 'Cet email est déjà utilisé.',
            'email.regex' => 'L\'adresse email doit se terminer par @ujkz.bf.',
            'structure_id.exists' => 'La structure sélectionnée n\'existe pas.'
        ]);
        
        // Générer un mot de passe aléatoire sécurisé
        $motDePasse = Str::random(16);
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
            Log::info('User créé avec succès');

            // Envoi d'e-mail en arrière-plan (ne bloque pas la réponse)
            try {
                $messageContent = "Bonjour, bienvenue sur notre plateforme ! Veuillez réinitialiser votre mot de passe via le lien de connexion.";
                EmailService::sendEmail($user->email, $messageContent);
            } catch (\Exception $mailException) {
                Log::warning("Email non envoyé pour {$user->email} : {$mailException->getMessage()}");
            }

            return response()->json([
                'message' => 'User créé avec succès',
                'User' => $user,
            ], 201);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la création de l'User : {$e->getMessage()}");
            return response()->json([
                'message' => 'Erreur lors de la création de l\'utilisateur.',
            ], 500);
            
        }
    }
    /**
     * Mettre à jour les informations d'un User.
     */
    public function update(Request $request, $id)
{
    try {
        $this->verifyIsAdmin();
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
        ], 500);
    }
}

    /**
     * Réinitialiser le mot de passe d'un User.
     */
    public function resetPassword($id)
    {
        try {
            $this->verifyIsAdmin();
            $user = User::findOrFail($id);

            // Générer un mot de passe aléatoire sécurisé
            $motDePasse = Str::random(16);
            $user->password = Hash::make($motDePasse);
            $user->save();

            $messageContent = "Bonjour, votre mot de passe a été réinitialisé. Veuillez utiliser le lien de connexion pour définir un nouveau mot de passe.";
            EmailService::sendEmail($user->email, $messageContent);
            return response()->json([
                'message' => 'Mot de passe réinitialisé avec succès.',
            ], 200);
            // Envoi d'un e-mail à l'utilisateur
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de la réinitialisation du mot de passe : {$e->getMessage()}");
            return response()->json([
                'message' => 'Erreur lors de la réinitialisation du mot de passe.',
            ], 500);
        }
    }
    public function supprimerUtilisateur($id)
{
    try {
        $this->verifyIsAdmin();
    } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
        throw $e;
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 403);
    }
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
