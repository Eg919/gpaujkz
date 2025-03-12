<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ActivitesImport;
use Maatwebsite\Excel\Facades\Excel;


class ActiviteImportsController extends Controller
{
    public function import(Request $request)
    {
        // Validation du fichier Excel
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            // Importer les données
            Excel::import(new ActivitesImport, $request->file('file'));

            return response()->json(['message' => 'Importation réussie!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'importation'], 500);
        }
    }
}
