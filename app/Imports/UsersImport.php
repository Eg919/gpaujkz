<?php

namespace App\Imports;
use App\Models\User;
use App\Models\Structure;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Support\Str;
use App\Services\EmailService;
use Illuminate\Support\Collection;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation, WithCustomCsvSettings
{
    private $rowCount = 0;
    private static $allowedRoles = ['Utilisateur', 'Responsable', 'Administrateur'];

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';'
        ];
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $structure = Structure::where('sigle', $row['sigle_structure'])->first();
            
            $role = in_array($row['role'] ?? '', self::$allowedRoles) ? $row['role'] : 'Utilisateur';
            
            $user = User::where('email', $row['email'])->first();
            
            if (!$user) {
                $motDePasse = Str::random(16);
                $user = User::create([
                    'email' => $row['email'],
                    'password' => Hash::make($motDePasse),
                    'role' => $role,
                    'etat' => $row['etat'] ?? 'Actif',
                    'structure_id' => $structure->id,
                ]);

                try {
                    EmailService::sendEmail($user->email, "Bonjour, bienvenue sur notre plateforme ! Veuillez réinitialiser votre mot de passe via le lien de connexion pour accéder à votre compte.");
                } catch (\Exception $e) {
                    // Ignorer les erreurs d'envoi d'email
                }

                $this->rowCount++;
            } else {
                $user->update([
                    'role' => $role,
                    'etat' => $row['etat'] ?? $user->etat,
                    'structure_id' => $structure->id,
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'sigle_structure' => 'required|exists:structures,sigle',
        ];
    }
    
    public function getRowCount()
    {
        return $this->rowCount;
    }
}