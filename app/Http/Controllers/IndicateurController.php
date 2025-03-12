<?php
namespace App\Http\Controllers;

use App\Models\Indicateur;
use App\Models\Activite;
use Illuminate\Http\Request;

class IndicateurController extends Controller
{
    // Méthode pour enregistrer un indicateur
    public function store(Request $request)
    {
        // Validation des données envoyées par le formulaire
        $validated = $request->validate([
            'indicateur' => 'required|string|max:255',
            'unite' => 'required|string|max:255',
            'reference' => 'nullable|integer',
            'cible' => 'nullable|integer',
            'activite_id' => 'required|exists:activites,id',
        ]);

        // Enregistrer l'indicateur
        $indicateur = Indicateur::create($validated);

        return response()->json(['message' => 'Indicateur enregistré avec succès', 'indicateur' => $indicateur], 200);
    }
}

