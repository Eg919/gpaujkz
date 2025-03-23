<?php

namespace App\Imports;
use App\Models\User;
use App\Models\Structure;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use App\Services\EmailService;
class UsersImport implements ToModel, WithHeadingRow
{
    private $rowCount = 0;

public function model(array $row)
{
    $structure = Structure::where('sigle', $row['sigle_structure'])->first();
    if (!$structure) return null; 

    $mois = Carbon::now()->format('m');
    $annee = Carbon::now()->format('Y');
    $motDePasse = 'Gpaujkz' . $mois . $annee;

    $user = User::create([
        'email' => $row['email'],
        'password' => Hash::make($motDePasse),
        'role' => $row['role'],
        'etat' => $row['etat'],
        'structure_id' => $structure->id,
    ]);

    EmailService::sendEmail($user->email, "Bonjour, votre mot de passe est : $motDePasse");

    $this->rowCount++; // Incrémentation du compteur
    return $user;
}

public function getRowCount()
{
    return $this->rowCount;
}
}