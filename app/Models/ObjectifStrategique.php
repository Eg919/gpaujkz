<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjectifStrategique extends Model
{
    use HasFactory;
    protected $table = 'objectifs_strategiques';
    protected $fillable = ['libelle', 'axe_strategique_id'];

    public function axe()
    {
        return $this->belongsTo(AxeStrategique::class, 'axe_strategique_id');
    }

    // Dans le modèle ObjectifStrategique.php
public function effetsAttendus()
{
    return $this->hasMany(EffetAttendu::class, 'objectif_strategique_id');
}

    public function activite()
    {
        return $this->hasMany(Activite::class);
    }
}
