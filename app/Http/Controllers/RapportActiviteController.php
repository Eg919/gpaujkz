<?php

namespace App\Http\Controllers;
use App\Models\RapportActivite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RapportActiviteController extends Controller
{
    public function getTauxByActiviteAndTrimestre($activiteId, $trimestre)
{
    // Récupérer uniquement le taux en fonction de l'ID de l'activité et du trimestre
    $rapport = RapportActivite::where('activite_id', $activiteId)
                              ->where('trimestre', $trimestre)
                              ->first();

    if ($rapport !== null) {
        return response()->json(['rapporttaux' => $rapport]);
    } else {
        return response()->json(['message' => 'Taux non trouvé'], 404);
    }
}
public function getCouteByActiviteAndTrimestre($activiteId, $trimestre)
{
    // Récupérer uniquement le coût en fonction de l'ID de l'activité et du trimestre
    $rapport = RapportActivite::where('activite_id', $activiteId)
                              ->where('trimestre', $trimestre)
                              ->first();

    if ($rapport !== null) {
        return response()->json(['rapportcoute' => $rapport]);
    } else {
        return response()->json(['message' => 'Coût non trouvé'], 404);
    }
}


public function getRapportByActiviteAndTrimestre($activiteId, $trimestre)
{
    try {
        // Valider les paramètres
        Log::info("Début de la validation des paramètres pour l'ID d'activité : {$activiteId} et le trimestre : {$trimestre}");
        $activiteId = intval($activiteId);
        $trimestre = intval($trimestre);

        if ($activiteId <= 0 || $trimestre < 1 || $trimestre > 4) {
            Log::warning("Paramètres invalides reçus : activiteId={$activiteId}, trimestre={$trimestre}");
            return response()->json(['error' => 'Paramètres invalides'], 400);
        }

        // Rechercher le rapport correspondant dans la table rapports_activites
        Log::info("Recherche du rapport pour l'ID d'activité : {$activiteId} et le trimestre : {$trimestre}");
        $rapport = RapportActivite::where('activite_id', $activiteId)
                                  ->where('trimestre', $trimestre)
                                  ->first();

        if (!$rapport) {
            Log::info("Aucun rapport trouvé pour l'ID d'activité : {$activiteId} et le trimestre : {$trimestre}");

            // Si aucun rapport n'est trouvé, vérifier si l'activité existe
            Log::info("Vérification de l'existence de l'activité avec l'ID : {$activiteId}");
            $activityExists = \App\Models\Activite::find($activiteId);

            if (!$activityExists) {
                Log::warning("Aucune activité trouvée avec l'ID : {$activiteId}");
                return response()->json(['message' => 'Aucune activité trouvée avec cet ID.'], 404);
            }

            // Si l'activité existe mais qu'il n'y a pas de rapport, retourner des valeurs par défaut
            Log::info("Activité trouvée, mais aucun rapport disponible pour le trimestre : {$trimestre}");
            return response()->json([
                'message' => 'Aucun rapport trouvé pour cette activité et ce trimestre.',
                'taux' => 0,
                'coute' => 0,
            ], 200);
        }

        // Retourner le rapport au format JSON
        Log::info("Rapport trouvé pour l'ID d'activité : {$activiteId} et le trimestre : {$trimestre}", [
            'taux' => $rapport->taux ?? 0,
            'coute' => $rapport->coute ?? 0,
        ]);
        return response()->json([
            'taux' => $rapport->taux ?? 0,
            'coute' => $rapport->coute ?? 0,
        ]);
    } catch (\Exception $e) {
        // Gestion des erreurs inattendues
        Log::error("Une erreur est survenue lors de la récupération du rapport", [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => 'Une erreur est survenue lors de la récupération du rapport.', 'details' => $e->getMessage()], 500);
    }
}
}
