<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Structure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestUsersSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'Administrateur',
            'Chef-de-service',
            'Responsable-de-structure',
            'Point-Focale',
            'Ordonnateur',
            'Administrateur_DSI',
            'Planificateur'
        ];

        // Ensure there is at least one structure
        $structure = Structure::first();
        if (!$structure) {
            $structure = Structure::create([
                'nom_structure' => 'Structure de Test',
                'description' => 'Structure générée pour les tests',
                // add any required fields for structure
            ]);
        }

        $password = Hash::make('password123');

        foreach ($roles as $role) {
            $email = strtolower(str_replace('_', '', str_replace('-', '', $role))) . '@ujkz.bf';
            
            // Check if user already exists
            if (!User::where('email', $email)->exists()) {
                User::create([
                    'email' => $email,
                    'password' => $password,
                    'role' => $role,
                    'structure_id' => $structure->id,
                    'etat' => 'Actif',
                ]);
            }
        }
        
        echo "Comptes de test crees avec succes. Mot de passe pour tous : password123\n";
    }
}
