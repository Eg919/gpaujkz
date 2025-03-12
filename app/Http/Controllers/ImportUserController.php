<?php
namespace App\Http\Controllers;
use App\Services\EmailService;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportUserController extends Controller
{
    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls'
    ]);

    try {
        // Importer les utilisateurs et récupérer la liste des nouveaux utilisateurs
        $import = new UsersImport();
        Excel::import($import, $request->file('file'));

        // Récupérer les utilisateurs importés (à adapter selon ta logique)
        $utilisateurs = User::latest()->take($import->getRowCount())->get();

        foreach ($utilisateurs as $user) {
            // Générer un mot de passe aléatoire
           // Nouveau mot de passe par défaut
           $mois = Carbon::now()->format('m'); // Mois actuel
           $annee = Carbon::now()->format('Y'); // Année actuelle
           // Générer un mot de passe aléatoire
           $motDePasse = 'Gpaujkz'.$mois.$annee;
           $user->password = Hash::make($motDePasse);
           $user->save();
            // Envoyer un email de bienvenue avec le mot de passe temporaire
            $messageContent = "Bonjour {$user->nom}, bienvenue sur notre plateforme ! 
            Votre mot de passe par défaut est : **$motDePasse**. Vous devez le changer à la première connexion.";

            EmailService::sendEmail($user->email, $messageContent);
        }

        return response()->json(['message' => 'Utilisateurs importés et emails envoyés avec succès !'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur lors de l\'importation : ' . $e->getMessage()], 500);
    }
}
}

