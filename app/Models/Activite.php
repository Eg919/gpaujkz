<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activite extends Model
{
    use HasFactory;

    // Définir les colonnes qui peuvent être assignées en masse (mass assignable)
    protected $fillable = [
        'libelle',
        'etat_financier',
        'etat_slection',
        'etat_activite',
        'partenaire',
        'hort_progamme',
        'finance_partenaire',
        'etat',
        'finance_etat',
        'trimestre_1',
        'trimestre_2',
        'trimestre_3',
        'trimestre_4',
        'soumi',
        'reconduir',
        //rapports activites
        'coute_t1',
        'taux_t1',
        'coute_t2',
        'taux_t2',
        'coute_t3',
        'taux_t3',
        'coute_t4',
        'taux_t4',
        'confirmation_presi',
        'observation',
        'user_id',
        'effets_attendus_id',
        'objectif_strategique_id',
        'structure_id',
        'sessions_id',
    ];


    // Définir les relations avec d'autres modèles

    // Relation avec l'utilisateur (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec les effets attendus (EffetAttendu)
    public function effetsAttendus()
    {
        return $this->belongsTo(EffetAttendu::class, 'effets_attendus_id');
    }

    // Relation avec l'axe stratégique (AxeStrategique)
    public function objectifStrategique()
    {
        return $this->belongsTo(ObjectifStrategique::class, 'objectif_strategique_id');
    }

    public function rapportTrimestriel($trimestre)
    {
        return $this->rapportsActivites()->where('trimestre', $trimestre)->first();
    }
    // Relation avec les sessions (SessionActivite)
    public function session()
    {
        return $this->belongsTo(SessionActivite::class, 'sessions_id');
    }
    public function taches()
    {
        return $this->hasMany(Tache::class, 'activite_id');
    }
    
    public function structure()
    {
        return $this->belongsTo(Structure::class, 'structure_id');
    }
    
    public function indicateurs()
    {
        return $this->hasMany(Indicateur::class, 'activite_id');
    }
    
}
