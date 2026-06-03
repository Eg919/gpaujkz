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
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
                        $fail('Le fichier doit être de type : xlsx, xls, csv.');
                    }
                }
            ]
        ]);

        try {
            $import = new UsersImport();
            Excel::import($import, $request->file('file'));

            return response()->json(['message' => $import->getRowCount() . ' nouveaux utilisateurs importés avec succès !'], 200);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            return response()->json(['message' => 'Erreur de validation.', 'errors' => $failures], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de l\'importation. Veuillez vérifier le fichier et réessayer.', 'error' => $e->getMessage()], 500);
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="modele_import_utilisateurs.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['email', 'role', 'etat', 'sigle_structure'], ';');
            fputcsv($file, ['egis@example.com', 'Utilisateur', 'Actif', 'DSI'], ';');
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

