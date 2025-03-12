<?php

namespace App\Providers;
use App\Models\User;
use App\Models\Structure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use App\Services\EmailService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    // Schema::defaultStringLength(191);

    //     // Vérifier si la structure "GPAADMIN" existe, sinon la créer
    //     $structure = Structure::where('sigle', 'DEPS')->first();

    //     if (!$structure) {
    //         $structure = Structure::create([
    //             'libelle_structure' => 'Direction des Études, de la Planification et des Statistiques',
    //             'sigle' => 'DEPS',
    //             'etat' => 'actif'
    //         ]);
    //     }

    //     // Vérifier si l'utilisateur existe avant de l'insérer
    //     $existingUser = User::where('email', 'gpaujkz@gmail.com')->first();

    //     if (!$existingUser) {
    //         // Générer un mot de passe aléatoire sécurisé
    //         $motDePasse = Str::random(12); // Génère un mot de passe de 12 caractères
            
    //         // Création de l'utilisateur
    //         $user = User::create([
    //             'nom' => 'Admin',
    //             'prenom' => 'GPA',
    //             'email' => 'gpaujkz@gmail.com',
    //             'password' => Hash::make($motDePasse),
    //             'role' => 'Administrateur',
    //             'etat' => 'actif',
    //             'structure_id' => $structure->id, // Associer la structure
    //         ]);

    //         // Envoyer l'email après la création de l'utilisateur
    //         $messageContent = "Bonjour Monsieur/Madame {$user->nom} {$user->prenom}, 
    //         bienvenue sur notre plateforme ! Votre mot de passe par défaut est : $motDePasse. 
    //         Vous devez le changer à la première connexion.";
            
    //         EmailService::sendEmail($user->email, $messageContent);
    //     }
    }
}
