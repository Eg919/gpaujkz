<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionActivite extends Model
{
    use HasFactory;

    protected $table = 'sessions_activites';

    protected $fillable = [
        'annee',
        'etat',
        'date_debut',
        'date_fin',
        'plan_strategiques_id',
    ];
    public function activite()
    {
        return $this->hasMany(Activite::class);
    }
    
}
