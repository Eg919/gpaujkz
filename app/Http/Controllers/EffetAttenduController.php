<?php
namespace App\Http\Controllers;

use App\Models\EffetAttendu;
use Illuminate\Http\Request;
use App\Models\AxeStrategique;
use App\Models\Activite;

class EffetAttenduController extends Controller
{
    /**
     * Affiche une liste des effets attendus.
     */
    public function index()
    {
        $effets = EffetAttendu::with('objectif')->get();
        return response()->json($effets);
    }
    public function getEffetsAttendusParAxe($axeId)
{
    // Charger l'axe stratégique avec les objectifs et effets attendus en une seule requête
    $axeStrategique = AxeStrategique::with('objectifsStrategiques.effetsAttendus')->find($axeId);

    if (!$axeStrategique) {
        return response()->json(['message' => 'Axe stratégique non trouvé'], 404);
    }

    // Récupérer les effets attendus associés à cet axe stratégique
    $effetsAttendus = $axeStrategique->objectifsStrategiques->flatMap(function ($objectif) {
        return $objectif->effetsAttendus;
    });

    return response()->json($effetsAttendus);
}

    /**
     * Enregistre un ou plusieurs effets attendus.
     */
    public function store(Request $request)
    {
        $request->validate([
            'objectif_strategique_id' => 'required|exists:objectifs_strategiques,id',
            'effets' => 'required|array',
            'effets.*.libelle' => 'required|string|max:255',
        ]);

        $effetsCrees = []; // Tableau pour stocker les effets créés

        foreach ($request->effets as $effetData) {
            $effet = new EffetAttendu();
            $effet->libelle = $effetData['libelle'];
            $effet->objectif_strategique_id = $request->objectif_strategique_id;
            $effet->save();
            $effetsCrees[] = $effet; // Ajout de l'effet créé au tableau
        }

        return response()->json([
            'message' => 'Effet(s) attendu(s) créé(s) avec succès.',
            'effets' => $effetsCrees // Renvoie également les effets créés
        ], 201);
    }

    // Mettre à jour un effet attendu
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
        ]);

        $effet = EffetAttendu::findOrFail($id);
        $effet->update($validated);

        return response()->json($effet);
    }
    public function supprimerEffet($id)
    {
        $effet = EffetAttendu::findOrFail($id);
$activites=Activite::where('effet_attendu_id',$id)->get();

if(count($activites)>0){
    return response()->json(['message' => 'Impossible de supprimer cet effet attendu car il est associé à des activités'], 400);
}
        $effet->delete();

        return response()->json(['message' => 'Effet attendu supprimé avec succès.']);
    }
}
