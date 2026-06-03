<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activite extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    // Définir les colonnes qui peuvent être assignées en masse (mass assignable)
    protected $fillable = [
        'libelle',
        'etat_financier',
        'etat_slection',
        'etat_activite',
        'partenaire',
        'partenaires_list',
        'motif_rejet',
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
        'motif_rejet',
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
    
    public function structuresPartenaires()
    {
        return $this->belongsToMany(Structure::class, 'activite_structure_partenaire', 'activite_id', 'structure_id')->withTimestamps();
    }
    
    public function indicateurs()
    {
        return $this->hasMany(Indicateur::class, 'activite_id');
    }

    /**
     * Accesseur : retourne les partenaires depuis partenaires_list (JSON)
     * ou fallback sur l'ancien champ partenaire (string) pour compatibilité.
     */
    public function getPartenairesDetailsAttribute()
    {
        $partenaires = [];


        // Ajouter les partenaires de la liste JSON
        if (!empty($this->partenaires_list)) {
            foreach ($this->partenaires_list as $p) {
                $partenaires[] = $p;
            }
        } elseif (!empty($this->partenaire)) {
            // Fallback : ancien champ string
            $partenaires[] = [
                'nom' => $this->partenaire,
                'montant' => $this->finance_partenaire ?? 0,
            ];
        }

        return $partenaires;
    }

    /**
     * Accesseur : retourne le sigle de la structure du créateur de l'activité.
     * Priorise la structure de l'utilisateur qui a créé l'activité.
     */
    public function getStructureSigleAttribute()
    {
        // On récupère le sigle de la structure de l'utilisateur qui a créé l'activité (créateur)
        if ($this->user && $this->user->structure) {
            return $this->user->structure->sigle;
        }

        // Si l'utilisateur n'est pas chargé ou n'a pas de structure, fallback sur la relation directe
        if ($this->structure) {
            return $this->structure->sigle;
        }

        return 'UKZ';
    }

    protected $appends = ['partenaires_details', 'structure_sigle'];

    protected $casts = [
        'partenaires_list' => 'array',
    ];
    
}
