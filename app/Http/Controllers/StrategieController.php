<?php

namespace App\Http\Controllers;

use App\Models\AxeStrategique;
use App\Models\ObjectifStrategique;
use App\Models\EffetAttendu;
use App\Models\SessionActivite;
use Illuminate\Http\Request;

class StrategieController extends Controller
{
    /**
     * Récupérer les axes stratégiques en fonction de l'ID du plan stratégique sélectionné.
     */
    public function getAxesByPlan($planId)
    {
        $axes = AxeStrategique::where('plan_strategique_id', $planId)->get();

        return response()->json($axes);
    }

    /**
     * Récupérer les objectifs stratégiques en fonction de l'ID de l'axe stratégique sélectionné.
     */
    public function getObjectifsByAxe($axeId)
    {
        $objectifs = ObjectifStrategique::where('axe_strategique_id', $axeId)->get();

        return response()->json($objectifs);
    }

    /**
     * Récupérer les effets attendus en fonction de l'ID de l'objectif stratégique sélectionné.
     */
    public function getEffetsByObjectif($objectifId)
    {
        $effets = EffetAttendu::where('objectif_strategique_id', $objectifId)->get();

        return response()->json($effets);
    }
    public function getEffetsByObjectifActivite($objectifId)
    {
        // Obtenir la session en cours (par exemple, où l'état est 'en cours')
        $sessionEnCours = SessionActivite::where('etat', 'Ouvert')->first();

        if (!$sessionEnCours) {
            return response()->json(['message' => 'Aucune session en cours trouvée.'], 404);
        }

        $effets = EffetAttendu::where('objectif_strategique_id', $objectifId)
            ->whereHas('activite', function ($query) use ($sessionEnCours) {
                $query->where('sessions_id', $sessionEnCours->id)
                      ->orWhere('reconduir', $sessionEnCours->annee)
                      ->where('hort_progamme', 0);
            })->get();

        return response()->json($effets);
    }
    


}
