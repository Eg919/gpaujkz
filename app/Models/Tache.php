<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tache extends Model
{
    use HasFactory;

    protected $table = 'taches';

    protected $fillable = [
        'libelle',
        'pourcentage_tache',
        'taux_execution_tache',
        'etat',
        'activite_id',
    ];

    // Relation avec l'activite
    public function activite()
    {
        return $this->belongsTo(Activite::class, 'activite_id');
    }
}
