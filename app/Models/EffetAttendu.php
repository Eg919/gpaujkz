<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EffetAttendu extends Model
{
    use HasFactory;
    protected $table = 'effets_attendus';

    protected $fillable = ['libelle', 'objectif_strategique_id'];

    public function objectif()
    {
        return $this->belongsTo(ObjectifStrategique::class);
    }
    public function activite()
    {
        return $this->hasMany(Activite::class, 'effets_attendus_id');
    }
}

