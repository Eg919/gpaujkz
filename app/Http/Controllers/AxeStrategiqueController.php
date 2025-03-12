<?php

namespace App\Http\Controllers;

use App\Models\AxeStrategique;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\PlanStrategique;

class AxeStrategiqueController extends Controller
    {
        public function index(Request $request): JsonResponse
        {
        $planId = $request->query('plan_id');

        if (!$planId) {
            return response()->json(['message' => 'Le plan_id est requis.'], 400);
        }

        $axes = AxeStrategique::where('plan_strategique_id', $planId)->with('objectifs.effets')->get();
        return response()->json($axes);
        }
        public function getAxesStrategiquesEnCours()
        {
            // Récupérer le plan stratégique en cours
            $planStrategiqueEnCours = PlanStrategique::where('etat', 'Ouvert')->first();

            if (!$planStrategiqueEnCours) {
                return response()->json(['message' => 'Aucun plan stratégique en cours trouvé'], 404);
            }

            // Récupérer les axes stratégiques associés à ce plan stratégique
            $axesStrategiques = AxeStrategique::where('plan_strategique_id', $planStrategiqueEnCours->id)
                                            ->get();

            return response()->json($axesStrategiques);
        }

        public function store(Request $request): JsonResponse
        {
            // Validation des données
            $data = $request->validate([
                'axes' => 'required|array',
                'axes.*.libelle' => 'required|string|max:255',
                'plan_strategique_id' => 'required|exists:plans_strategiques,id',
            ]);

            // Insertion de chaque axe dans la base de données
            $axes = [];
            foreach ($data['axes'] as $axeData) {
                $axes[] = AxeStrategique::create([
                    'libelle' => $axeData['libelle'],
                    'plan_strategique_id' => $data['plan_strategique_id'],
                ]);
            }

            return response()->json(['message' => 'Axes stratégiques créés avec succès', 'axes' => $axes], 201);
        }

        // Mettre à jour un axe stratégique
        public function update(Request $request, $id)
        {
            $validated = $request->validate([
                'libelle' => 'required|string|max:255',
            ]);

            $axe = AxeStrategique::findOrFail($id);
            $axe->update($validated);

            return response()->json($axe);
        }
}
