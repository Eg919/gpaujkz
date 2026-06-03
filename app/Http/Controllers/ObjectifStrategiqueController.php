<?php
namespace App\Http\Controllers;
use App\Models\EffetAttendu;
use App\Models\ObjectifStrategique;
use Illuminate\Http\Request;
use App\Models\SessionActivite;
class ObjectifStrategiqueController extends Controller
{
    /**
     * Affiche une liste des objectifs stratégiques.
     */
    public function index()
    {
        $objectifs = ObjectifStrategique::with('axe')->get();
        return response()->json($objectifs);
    }

    /**
     * Enregistre plusieurs objectifs stratégiques.
     */
    public function store(Request $request)
    {
        $request->validate([
            'axe_strategique_id' => 'required|exists:axes_strategiques,id',
            'objectifs' => 'required|array',
            'objectifs.*.libelle' => 'required|string|max:255',
        ]);

        $objectifs = [];

        foreach ($request->objectifs as $objectifData) {
            $objectif = new ObjectifStrategique();
            $objectif->libelle = $objectifData['libelle'];
            $objectif->axe_strategique_id = $request->axe_strategique_id; // Utiliser l'ID passé dans la requête
            $objectif->save();
            $objectifs[] = $objectif; // Ajouter à la liste des objectifs créés
        }

        return response()->json([
            'message' => 'Objectifs stratégiques créés avec succès',
            'objectifs' => $objectifs,
        ]);
    }

    // Mettre à jour un objectif stratégique
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
        ]);

        $objectif = ObjectifStrategique::findOrFail($id);
        $objectif->update($validated);

        return response()->json($objectif);
    }
    public function objectifsStrategiquesEnCours()
{
    $objectifs = ObjectifStrategique::with('axe.plan')  // Charger la relation axe avec le plan
        ->whereHas('axe.plan', function ($query) {
            $query->where('etat', 'Ouvert');  // Vérifier que le plan est en cours
        })
        ->select('id', 'libelle', 'axe_strategique_id')  // Sélectionner les champs nécessaires
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $objectifs
    ]);

}

public function objectifsStrategiquesEnCoursAvecActivites(Request $request)
{
    $sessionId = $request->query('session_id');
    
    if ($sessionId) {
        $session = SessionActivite::find($sessionId);
    } else {
        $session = SessionActivite::where('etat', 'Ouvert')->first();
    }

    if (!$session) {
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    $objectifs = ObjectifStrategique::with(['axe.plan'])  
        ->whereHas('activite', function ($query) use ($session) {
            $query->where(function ($q) use ($session) {
                $q->where('sessions_id', $session->id)
                  ->orWhere('reconduir', $session->annee);
            })
            ->where('etat_slection', 'Validé')
            ->where('hort_progamme', 0); 
        })
        ->select('id', 'libelle', 'axe_strategique_id')  
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $objectifs
    ]);
}
public function supprimerObjectif($id)
{
    $objectif = ObjectifStrategique::findOrFail($id);
    $effets = EffetAttendu::where('objectif_strategique_id', $id)->get();
    if ($effets->count() > 0) {
        return response()->json([
            'status' => 'error',
            'message' => 'Cet objectif a des effets attendus associés. Veuillez les supprimer d\'abord.'
        ], 400);
    }

    $objectif->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Objectif supprimé avec succès.'
    ]);
}

}


