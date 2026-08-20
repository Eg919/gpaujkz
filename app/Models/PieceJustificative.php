<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PieceJustificative extends Model
{
    use HasFactory;

    protected $table = 'pieces_justificatives';

    protected $fillable = [
        'nom_fichier',
        'chemin_fichier',
        'type_fichier',
        'taille',
        'tache_id',
    ];

    public function tache()
    {
        return $this->belongsTo(Tache::class, 'tache_id');
    }
}
