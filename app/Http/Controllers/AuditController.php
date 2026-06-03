<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    /**
     * Get a paginated list of audits.
     */
    public function index(Request $request)
    {
        // On récupère les audits avec une jointure sur la table users et structures
        // pour avoir l'email, le rôle et la structure de la personne qui a fait l'action.
        $query = DB::table('audits')
            ->leftJoin('users', 'audits.user_id', '=', 'users.id')
            ->leftJoin('structures', 'users.structure_id', '=', 'structures.id')
            ->select('audits.*', 'users.email as user_email', 'users.role as user_role', 'structures.sigle as structure_sigle')
            ->orderBy('audits.created_at', 'desc');

        // Optional filter by action
        if ($request->has('action') && $request->input('action')) {
            $query->where('audits.action', $request->input('action'));
        }

        // Optional filter by email
        if ($request->has('email') && $request->input('email')) {
            $query->where('users.email', 'like', '%' . $request->input('email') . '%');
        }

        $audits = $query->paginate(15);

        // Décoder les champs JSON pour le frontend
        $audits->getCollection()->transform(function ($audit) {
            $audit->old_values = $audit->old_values ? json_decode($audit->old_values) : null;
            $audit->new_values = $audit->new_values ? json_decode($audit->new_values) : null;
            return $audit;
        });

        return response()->json($audits);
    }
}
