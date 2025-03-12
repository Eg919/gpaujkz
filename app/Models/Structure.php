<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Structure extends Model
{
    use HasFactory;
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
    ];
    public function activite()
    {
        return $this->hasMany(Activite::class);
    }
    public function user()
    {
        return $this->hasMany(User::class);
    }
}
