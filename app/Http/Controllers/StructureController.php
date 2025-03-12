<?php

namespace App\Http\Controllers;

use App\Models\Structure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StructureController extends Controller
{
    /**
     * Affiche la liste des structures.
     */
    public function index()
{
    // Remplacez 'created_at' par le champ selon lequel vous voulez trier les structures
    $structures = Structure::where('sigle', '!=', '') // Exclure GPAADMIN
    ->orderBy('id', 'desc')
    ->get();

    return response()->json($structures);
}

    /**
     * Enregistre une nouvelle structure.
     */
    public function store(Request $request)
{
    // Validation des données d'entrée
    $validated = $request->validate([
        'libelle_structure' => 'required|string|max:255',
        'sigle' => 'required|string|max:10',
        'etat' => 'required|string|max:50',
    ]);

    // Création de la nouvelle structure avec les données validées
    $structure = Structure::create($validated);

    return response()->json($structure, 201);
}

    /**
     * Met à jour une structure existante.
     */
    public function update(Request $request, $id)
{
    try {
        // Récupération de l'élément à mettre à jour
        $structure = Structure::findOrFail($id);

        // Validation des données d'entrée
        $validated = $request->validate([
            'libelle_structure' => 'required|string|max:255',
            'sigle' => 'required|string|max:10',
            'etat' => 'required|string|max:50',
        ]);

        // Mise à jour de la structure avec les données validées
        $structure->update($validated);

        return response()->json($structure); 

    } catch (\Exception $e) {
        \Log::error("Erreur lors de la mise à jour de la structure : {$e->getMessage()}");
        return response()->json(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], 500);
    }
}


    /**
     * Supprime une structure.
     */
    public function destroy($id)
    {
        try {
            $structure = Structure::findOrFail($id);
            $structure->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression de la structure : {$e->getMessage()}");
            return response()->json(['error' => 'Erreur lors de la suppression : ' . $e->getMessage()], 500);
        }
    }
    /**
     * Compter le nombre de structures.
     */
    public function count()
    {
        $structureCount = Structure::count(); // Compte le nombre total de structures
        $structureCount =($structureCount);
        return response()->json(['count' => $structureCount], 200);
    }
}
