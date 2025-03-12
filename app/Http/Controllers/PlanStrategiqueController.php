<?php
namespace App\Http\Controllers;

use App\Models\PlanStrategique;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PlanStrategiqueController extends Controller
{
    public function index(): JsonResponse
    {
        // Trier par created_at en ordre décroissant
        $plans = PlanStrategique::with('axes')->orderBy('created_at', 'desc')->get();
        return response()->json($plans);
    }

    public function getPlanActif()
    {
        // Récupérer le plan stratégique actif
        $planActif = PlanStrategique::where('etat', 'Ouvert')->first();

        // Si un plan actif est trouvé, renvoyer l'ID
        if ($planActif) {
            return response()->json([
                'id' => $planActif->id,
            ]);
        }

        // Sinon, renvoyer une réponse d'erreur
        return response()->json(['message' => 'Aucun plan stratégique en-cour trouvé.'], 404);
    }
    public function show(int $id): JsonResponse
    {
        $plan = PlanStrategique::with('axes.objectifsStrategiques.effetsAttendus')->findOrFail($id);
        return response()->json($plan);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'debut' => 'required|date',
            'fin' => 'required|date|after:debut',
            'etat' => 'required|string|max:255',
        ]);

        $plan = PlanStrategique::create($request->only(['titre', 'debut', 'fin', 'etat']));

        return response()->json(['message' => 'Plan stratégique créé avec succès', 'plan' => $plan]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'debut' => 'required|date',
            'fin' => 'required|date|after:debut',
            'etat' => 'required|string|max:255',
        ]);

        $plan = PlanStrategique::findOrFail($id);
        $plan->update($request->only(['titre', 'debut', 'fin', 'etat']));

        return response()->json(['message' => 'Plan stratégique mis à jour avec succès', 'plan' => $plan]);
    }

    public function destroy(int $id): JsonResponse
    {
        $plan = PlanStrategique::findOrFail($id);
        $plan->delete();

        return response()->json(['message' => 'Plan stratégique supprimé avec succès']);
    }
}
