<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Structure extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;
    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'structures';

    /**
     * Les attributs qui sont assignables.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'libelle_structure',
        'sigle',
        'etat',
        'masque',
    ];
    public function activite()
    {
        return $this->hasMany(Activite::class);
    }
    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function activitesPartenaires()
    {
        return $this->belongsToMany(Activite::class, 'activite_structure_partenaire', 'structure_id', 'activite_id')->withTimestamps();
    }
}
