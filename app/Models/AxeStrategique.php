<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AxeStrategique extends Model
{
    use HasFactory;
    protected $table = 'axes_strategiques';
    protected $fillable = ['libelle', 'plan_strategique_id'];

    public function plan()
{
    return $this->belongsTo(PlanStrategique::class, 'plan_strategique_id');
}


    // Dans le modèle AxeStrategique.php
public function objectifsStrategiques()
{
    return $this->hasMany(ObjectifStrategique::class, 'axe_strategique_id');
}

    
}
