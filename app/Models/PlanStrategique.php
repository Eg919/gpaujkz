<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanStrategique extends Model
{
    use HasFactory;

    protected $table = 'plans_strategiques';
    protected $fillable = ['titre', 'debut', 'fin', 'etat'];

    public function axes()
{
    return $this->hasMany(AxeStrategique::class, 'id');
}

    
}
