<?php

namespace App\Http\Controllers;
use App\Models\Structure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Méthode pour gérer la connexion de l'utilisateur
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validation des données de connexion
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        // Log pour suivre la tentative de connexion
        Log::info('Tentative de connexion', ['email' => $credentials['email']]);

        // Recherche de l'utilisateur par email
        $user = User::where('email', $credentials['email'])->first();

        // Vérification de l'existence de l'utilisateur
        if (!$user) {
            Log::warning('Utilisateur non trouvé', ['email' => $credentials['email']]);
            return response()->json(['message' => 'Informations incorrectes.'], 401);
        }

        // Tentative de connexion de l'utilisateur
        if (Auth::attempt($credentials)) {
            // Authentification réussie pour API
            $user = Auth::user();

            // Log pour authentification réussie
            Log::info('Utilisateur authentifié', ['user_id' => $user->id]);
            $mois = Carbon::now()->format('m'); // Mois actuel
            $annee = Carbon::now()->format('Y'); // Année actuelle
            // Générer un mot de passe aléatoire
            $motDePasse = 'Gpaujkz'.$mois.$annee;
            // Vérification si le mot de passe est le mot de passe par défaut
            if (Hash::check($motDePasse , $user->password)) {
                Log::info('Mot de passe par défaut', ['user_id' => $user->id]);
                return response()->json([
                    'message' => 'Mot de passe par défaut. Veuillez changer votre mot de passe.',
                    'redirect' => '/changer-mot-de-passe' // Rediriger vers la page de modification du mot de passe
                ]);
            } else {
                // Ou avec une condition explicite
                $structure = Structure::where('id', $user->structure_id)->first();
                if($user->etat= "actif" && $structure ->etat="actif"){
                    return response()->json([
                        'message' => 'Connexion réussie.',
                        'token' => $user->createToken('API Token')->plainTextToken,
                        'redirect' => '/admin' // Rediriger vers la page d'accueil ou tableau de bord
                    ]);
                }
                else{
                return response()->json([
                   'message' => "Votre structure ou votre compte est inactif veiellez contacter l'administrateur.",
                ]);
                }
            }
        } else {
            // Log si la tentative d'authentification échoue
            Log::warning('Échec de la tentative de connexion', ['email' => $credentials['email']]);
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }
    }

    /**
     * Méthode pour changer le mot de passe
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        // Validation des champs
        $request->validate([
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Récupérer l'utilisateur connecté via Sanctum
        $user = $request->user();

        // Vérifier que l'ancien mot de passe est correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect.'], 403);
        }

        // Mettre à jour le mot de passe
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json(['message' => 'Mot de passe changé avec succès. Vous avez été déconnecté.', 'redirect' => '/gestionRoles'], 200);
    }

    /**
     * Méthode pour déconnecter l'utilisateur
     *
     * @param Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        // Révoquer tous les tokens personnels de l'utilisateur
        $user->tokens()->delete();
        // Optionnel : invalider la session de l'utilisateur
        $request->session()->invalidate();
        // $request->session()->regenerateToken();
        return response()->json(['message' => 'Déconnexion réussie'], 200);
    }

    /**
     * Méthode optionnelle pour récupérer l'utilisateur connecté
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
