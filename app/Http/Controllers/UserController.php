<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use App\Models\Structure;
use Illuminate\Http\Request;
use App\Models\Activite;
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
    
        try {
            DB::beginTransaction(); // Démarre une transaction pour garantir l'intégrité des données
    
            // Calcul du trimestre courant
            $currentMonth = now()->month;
            $currentTrimester = (int) ceil($currentMonth / 3);
    
            // Récupération des activités en cours et validées
            $activites = Activite::where('etat_activite', 'en-attente')
                ->where('etat_slection', 'validé') // Correction faute de frappe
                ->where('structure_id', $user->structure_id)
                ->get();
    
            foreach ($activites as $activite) {
                $trimesterField = "trimestre_" . $currentTrimester;
                if (!empty($activite->$trimesterField)) {
                    if ($activite->taches()->count() === 0) {
                        $notificationExistante = NotificationActivite::where('user_id', $user->id)
                            ->where('activite_id', $activite->id)
                            ->exists();
                            Log::info( "cc'{$notificationExistante}' " );
                        if (!$notificationExistante) {
                            NotificationActivite::create([
                                'message'     => "L'activité '{$activite->libelle}' doit démarrer ce trimestre.",
                                'lu'          => 0,
                                'user_id'     => $user->id,
                                'activite_id' => $activite->id,
                            ]);
    
                            Log::info("Notification créée pour l'activité {$activite->id} et l'utilisateur {$user->id}");
                        }
                    }
                }
            }
    
            DB::commit(); // Valide la transaction
    
        } catch (\Exception $e) {
            DB::rollBack(); // Annule les changements en cas d'erreur
            Log::error("Erreur lors de l'enregistrement des notifications : " . $e->getMessage());
            return response()->json(['error' => 'Erreur interne du serveur'], 500);
        }
    
        // Récupérer les notifications non lues
        $notifications = NotificationActivite::where('user_id', $user->id)
            ->where('lu', 0)
            ->get();
    
        return response()->json([
            'role' => $user->role,
            'nom' => $user->nom,
            'email' => $user->email,
            'structure' => $user->structure ? [
                'id' => $user->structure->id,
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
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|max:50',
            'etat' => 'required|string|in:actif,inactif',
            'structure_id' => 'required|exists:structures,id',
        ], [
            'email.unique' => 'Cet email est déjà utilisé.',
            'structure_id.exists' => 'La structure sélectionnée n\'existe pas.'
        ]);

        // Mot de passe par défaut sécurisé
        $mois = Carbon::now()->format('m'); // Mois actuel
        $annee = Carbon::now()->format('Y'); // Année actuelle
        // Générer un mot de passe aléatoire
        $motDePasse = 'Gpaujkz'.$mois.$annee;

        try {
            DB::transaction(function () use ($validatedData, &$user, $motDePasse) {
                // Vérifier l'état de la structure
                $structure = Structure::findOrFail($validatedData['structure_id']);
                if ($structure->etat === 'inactif') {
                    $validatedData['etat'] = 'inactif'; // Mettre l'état de l'User à inactif
                } else {
                    // Si la structure est active, vérifier les Users actifs
                    $actifUsers = User::where('structure_id', $validatedData['structure_id'])
                        ->where('etat', 'actif')
                        ->get();
                }

                // Créer l'User
                $user = User::create([
                    'nom' => $validatedData['nom'],
                    'prenom' => $validatedData['prenom'],
                    'email' => $validatedData['email'],
                    'password' => Hash::make($motDePasse),
                    'role' => $validatedData['role'],
                    'etat' => $validatedData['etat'],
                    'structure_id' => $validatedData['structure_id'],
                ]);
            });

            Log::info('User créé:', $user->toArray());
            // Envoi d'un e-mail à l'utilisateur
            $messageContent = "Bonjour Monsieur/Madame {$user->nom}, bienvenue sur notre plateforme ! votre mot de passe par défaut est : $motDePasse que vous allez change à la première connexion.";
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
                'nom' => 'sometimes|string|max:255',
                'prenom' => 'sometimes|string|max:255',
                'sexe' => 'sometimes|string|max:10',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'role' => 'sometimes|string|max:50',
                'etat' => 'sometimes|string|in:actif,inactif',
                'structure_id' => 'sometimes|exists:structures,id',
                'password' => 'sometimes|string|min:8|confirmed', // Mot de passe optionnel avec confirmation
            ]);

            DB::transaction(function () use ($validatedData, $user) {
                // Vérifier si l'état de l'User doit être mis à "actif"
                if (isset($validatedData['etat']) && $validatedData['etat'] === 'actif') {
                    // Récupérer la structure de l'User
                    $structure = Structure::findOrFail($user->structure_id);

                    // Vérifier si la structure est inactive
                    if ($structure->etat === 'inactif') {
                        throw new \Exception("La structure est inactive. Impossible de passer l'User à l'état actif.");
                    }

                    // Si la structure est active, mettre les autres Users de la même structure à "inactif"
                    // User::where('structure_id', $user->structure_id)
                    //     ->where('etat', 'actif')
                    //     ->where('id', '!=', $user->id)
                    //     ->update(['etat' => 'inactif']);
                        
                }

                // Mettre à jour l'User avec les données validées
                $user->update(array_filter($validatedData));
            });

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
            $messageContent = "Bonjour Monsieur/Madame {$user->nom}, votre mot de passe a ete restorer avec sucess.  Utilise le mot de passe par défaut est : $motDePasse que vous allez change à la première connexion. ";
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
