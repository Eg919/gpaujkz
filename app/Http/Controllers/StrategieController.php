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
    public function getEffetsByObjectifActivite(Request $request, $objectifId)
    {
        $sessionId = $request->query('session_id');
        
        if ($sessionId) {
            $session = SessionActivite::find($sessionId);
        } else {
            $session = SessionActivite::where('etat', 'Ouvert')->first();
        }

        if (!$session) {
            return response()->json([], 200);
        }

        $effets = EffetAttendu::where('objectif_strategique_id', $objectifId)
            ->whereHas('activite', function ($query) use ($session) {
                $query->where(function($q) use ($session) {
                    $q->where('sessions_id', $session->id)
                      ->orWhere('reconduir', $session->annee);
                })
                ->where('hort_progamme', 0);
            })->get();

        return response()->json($effets);
    }
    


}
