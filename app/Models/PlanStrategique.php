<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanStrategique extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $table = 'plans_strategiques';
    protected $fillable = ['titre', 'debut', 'fin', 'etat'];

    public function axes()
{
    return $this->hasMany(AxeStrategique::class, 'id');
}

    
}
