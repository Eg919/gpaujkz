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
        Schema::defaultStringLength(191);

        // Ne pas exécuter si les tables n'existent pas encore (ex. pendant migrate)
        if (! Schema::hasTable('structures')) {
            return;
        }

        // Vérifier si la structure "DEPS" existe, sinon la créer
        $structure = Structure::where('sigle', 'DEPS')->first();

        if (!$structure) {
            $structure = Structure::create([
                'libelle_structure' => 'Direction des Études, de la Planification et des Statistiques',
                'sigle' => 'DEPS',
                'etat' => 'actif'
            ]);
        }

        // Vérifier si l'utilisateur existe avant de l'insérer
        $existingUser = User::where('email', 'gpaujkz@gmail.com')->first();

        if (!$existingUser) {
            // Générer un mot de passe aléatoire sécurisé
           // $motDePasse = Str::random(16);
           $motDePasse ='12345678';
            // Création de l'utilisateur
            $user = User::create([
                'nom' => 'Admin',
                'prenom' => 'GPA',
                'email' => 'gpaujkz@gmail.com',
                'password' => Hash::make($motDePasse),
                'role' => 'Administrateur_DSI',
                'etat' => 'Actif',
                'structure_id' => $structure->id,
            ]);

            // Envoyer un lien de réinitialisation au lieu du mot de passe en clair
            $messageContent = "Bonjour Monsieur/Madame le Gestionnaire, 
            bienvenue sur notre plateforme ! Veuillez réinitialiser votre mot de passe via le lien de connexion.";
            
            EmailService::sendEmail($user->email, $messageContent);
        }
    }
}
