<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indicateur extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    // Indique les colonnes qui peuvent être assignées en masse
    protected $fillable = [
        'libelle_indicateur', 
        'unite_indicateur', 
        'reference_indicateur', 
        'cible_indicateur', 
        'activite_id'
    ];

    // Relation avec le modèle Activite
    public function activite()
    {
        return $this->belongsTo(Activite::class);
    }
}
