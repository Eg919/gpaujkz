<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationActivite extends Model
{
    use HasFactory;

    // Définir la table associée (optionnel si le nom suit la convention Laravel)
    protected $table = 'notifications_activites';

    // Définir les attributs qui peuvent être assignés en masse (fillable)
    protected $fillable = [
        'message',
        'lu',
        'user_id',
        'activite_id',
    ];

    // Définir la relation avec le modèle Activite
    public function activite()
    {
        return $this->belongsTo(Activite::class, 'activite_id');
    }
    // Définir la relation avec le modèle User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
