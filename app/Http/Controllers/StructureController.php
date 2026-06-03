<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Structure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StructureController extends Controller
{
    private function verifyIsAdmin()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !in_array($user->role, ['Administrateur', 'Ordonnateur'])) {
            throw new \Exception("Accès non autorisé : vous n'avez pas les droits d'administration pour cette action.");
        }
    }
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
    try {
        $this->verifyIsAdmin();
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 403);
    }

    $validated = $request->validate([
        'libelle_structure' => 'required|string|max:255',
        'sigle' => 'required|string|max:50',
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
        $this->verifyIsAdmin();
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
            $this->verifyIsAdmin();
            $structure = Structure::findOrFail($id);
            $structure->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression de la structure : {$e->getMessage()}");
            return response()->json(['error' => 'Erreur lors de la suppression : ' . $e->getMessage()], 500);
        }
    }
    public function supprimerStructure($id)
{
    try {
        $this->verifyIsAdmin();
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 403);
    }

    // Vérifier si la structure est associée à un utilisateur
    $structureAssociee = DB::table('users')->where('structure_id', $id)->exists();

    if ($structureAssociee) {
        // Si la structure est associée à un utilisateur, la masquer
        DB::table('structures')->where('id', $id)->update(['masque' => 1]);

        return response()->json([
            'message' => 'La structure est associée à un utilisateur, elle a été masquée.'
        ], 200);
    } else {
        // Si la structure n'est pas associée, la supprimer définitivement
        DB::table('structures')->where('id', $id)->delete();

        return response()->json([
            'message' => 'La structure a été supprimée avec succès.'
        ], 200);
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
