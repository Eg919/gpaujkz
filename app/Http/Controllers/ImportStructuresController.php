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
        // Validation du fichier
        $request->validate([
            'file' => 'required|mimes:xlsx,xls' // Limite de 5MB
        ]);

        try {
            Excel::import(new StructuresImport, $request->file('file'));

            return response()->json([
                'message' => 'Structures importées avec succès !'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'importation des structures.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
