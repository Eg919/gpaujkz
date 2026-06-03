<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StructuresImport;
use Exception;

class ImportStructuresController extends Controller
{
    public function importStructures(Request $request)
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
            $import = new StructuresImport();
            Excel::import($import, $request->file('file'));

            return response()->json([
                'message' => $import->getRowCount() . ' structures importées avec succès !'
            ], 200);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            return response()->json(['message' => 'Erreur de validation.', 'errors' => $failures], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'importation des structures.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="modele_import_structures.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['libelle_structure', 'sigle', 'etat'], ';');
            fputcsv($file, ['Direction des Systemes d Information', 'DSI', 'Actif'], ';');
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
