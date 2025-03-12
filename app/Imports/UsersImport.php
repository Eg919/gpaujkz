<?php

namespace App\Imports;
use App\Models\User;
use App\Models\Structure;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Récupérer l'ID de la structure via le sigle
        $structure = Structure::where('sigle', $row['sigle_structure'])->first();

        if (!$structure) {
            return null; // Ignorer les lignes si la structure n'existe pas
        }

        return new User([
            'nom'          => $row['nom'],
            'prenom'       => $row['prenom'],
            'email'        => $row['email'],
            'password'     => Hash::make('password123'), // Remplacez par un mot de passe par défaut ou générez-en un
            'role'         => $row['role'],
            'etat'         => $row['etat'],
            'structure_id' => $structure->id,
        ]);
    }
    public function getRowCount()
    {
        return $this->rowCount;
    }
}

