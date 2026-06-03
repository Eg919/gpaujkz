<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\SessionActivite;
use App\Models\Structure;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Récupère les statistiques globales pour le tableau de bord (Admin)
     */
    public function getGlobalStats()
    {
        $session = SessionActivite::where('etat', 'Ouvert')->first();
        if (!$session) {
            return response()->json(['message' => 'Aucune session ouverte'], 200);
        }

        $confirmedCount = Activite::where('sessions_id', $session->id)
            ->where('confirmation_presi', 1)
            ->count();

        $totalFinished = Activite::where('sessions_id', $session->id)
            ->where('etat_activite', 'Terminée')
            ->count();

        // Distribution par état de sélection
        $selectionStats = Activite::where('sessions_id', $session->id)
            ->select('etat_slection', DB::raw('count(*) as total'))
            ->groupBy('etat_slection')
            ->get();

        // Distribution par état d'exécution
        $executionStats = Activite::where('sessions_id', $session->id)
            ->select('etat_activite', DB::raw('count(*) as total'))
            ->groupBy('etat_activite')
            ->get();

        // Top 5 structures par nombre d'activités confirmées (Le Programme)
        $structureStats = Structure::select('structures.sigle', DB::raw('count(activites.id) as activites_count'))
            ->join('activites', 'structures.id', '=', 'activites.structure_id')
            ->where('activites.sessions_id', $session->id)
            ->where('activites.confirmation_presi', 1)
            ->groupBy('structures.id', 'structures.sigle')
            ->orderByDesc('activites_count')
            ->limit(5)
            ->get();

        // Répartition des activités terminées par structure
        $finishedByStructure = Structure::select('structures.sigle', DB::raw('count(activites.id) as finished_count'))
            ->join('activites', 'structures.id', '=', 'activites.structure_id')
            ->where('activites.sessions_id', $session->id)
            ->where('activites.etat_activite', 'Terminée')
            ->groupBy('structures.id', 'structures.sigle')
            ->orderByDesc('finished_count')
            ->limit(5)
            ->get();

        // Statistiques financières globales
        $financialStats = Activite::where('sessions_id', $session->id)
            ->where('etat_slection', 'Validé')
            ->select(
                DB::raw('SUM(COALESCE(finance_etat, 0) + COALESCE(finance_partenaire, 0)) as total_budget'),
                DB::raw('SUM(COALESCE(coute_t1, 0) + COALESCE(coute_t2, 0) + COALESCE(coute_t3, 0) + COALESCE(coute_t4, 0)) as total_executed'),
                DB::raw('AVG(COALESCE(taux_t1, 0) + COALESCE(taux_t2, 0) + COALESCE(taux_t3, 0) + COALESCE(taux_t4, 0)) as avg_execution_rate')
            )
            ->first();

        return response()->json([
            'selection_distribution' => $selectionStats,
            'execution_distribution' => $executionStats,
            'structure_distribution' => $structureStats,
            'finished_by_structure' => $finishedByStructure,
            'financial_stats' => $financialStats,
            'confirmed_count' => $confirmedCount,
            'total_finished' => $totalFinished,
        ]);
    }

    /**
     * Récupère les statistiques spécifiques à une structure (Planificateur/Responsable)
     */
    public function getStructureStats()
    {
        $user = Auth::user();
        $session = SessionActivite::where('etat', 'Ouvert')->first();
        
        if (!$session || !$user->structure_id) {
            return response()->json(['message' => 'Données indisponibles'], 200);
        }

        $totalActivites = Activite::where('structure_id', $user->structure_id)
            ->where('sessions_id', $session->id)
            ->count();

        // Activités ayant au moins une tâche (planifiées)
        $plannedActivites = Activite::where('structure_id', $user->structure_id)
            ->where('sessions_id', $session->id)
            ->whereHas('taches')
            ->count();

        $confirmedStructureCount = Activite::where('structure_id', $user->structure_id)
            ->where('sessions_id', $session->id)
            ->where('confirmation_presi', 1)
            ->count();

        // Tâches par état pour la structure
        $tachesStats = Tache::join('activites', 'taches.activite_id', '=', 'activites.id')
            ->where('activites.structure_id', $user->structure_id)
            ->where('activites.sessions_id', $session->id)
            ->select('taches.etat_tache', DB::raw('count(*) as total'))
            ->groupBy('taches.etat_tache')
            ->get();

        // Distribution par état de sélection (Filtré par structure)
        $selectionStats = Activite::where('sessions_id', $session->id)
            ->where('structure_id', $user->structure_id)
            ->select('etat_slection', DB::raw('count(*) as total'))
            ->groupBy('etat_slection')
            ->get();

        // Distribution par état d'exécution (Filtré par structure)
        $executionStats = Activite::where('sessions_id', $session->id)
            ->where('structure_id', $user->structure_id)
            ->select('etat_activite', DB::raw('count(*) as total'))
            ->groupBy('etat_activite')
            ->get();

        // Finances de la structure
        $structureFinancials = Activite::where('structure_id', $user->structure_id)
            ->where('sessions_id', $session->id)
            ->select(
                DB::raw('SUM(COALESCE(finance_etat, 0) + COALESCE(finance_partenaire, 0)) as budget'),
                DB::raw('SUM(COALESCE(coute_t1, 0) + COALESCE(coute_t2, 0) + COALESCE(coute_t3, 0) + COALESCE(coute_t4, 0)) as executed')
            )
            ->first();

        return response()->json([
            'total_activites' => $totalActivites,
            'planned_activites' => $plannedActivites,
            'planning_rate' => $totalActivites > 0 ? round(($plannedActivites / $totalActivites) * 100) : 0,
            'taches_distribution' => $tachesStats,
            'selection_distribution' => $selectionStats,
            'execution_distribution' => $executionStats,
            'financials' => $structureFinancials,
            'confirmed_count' => $confirmedStructureCount,
            'total_finished' => Activite::where('structure_id', $user->structure_id)->where('sessions_id', $session->id)->where('etat_activite', 'Terminée')->count()
        ]);
    }

    /**
     * Récupère les statistiques pour l'Administrateur DSI (Utilisateurs et Structures)
     */
    public function getDsiStats()
    {
        $userCount = User::count();
        $structureCount = Structure::count();
        
        $roleDistribution = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get();

        $structureTypes = Structure::select('etat', DB::raw('count(*) as total'))
            ->groupBy('etat')
            ->get();

        return response()->json([
            'total_users' => $userCount,
            'total_structures' => $structureCount,
            'role_distribution' => $roleDistribution,
            'structure_types' => $structureTypes
        ]);
    }
}
