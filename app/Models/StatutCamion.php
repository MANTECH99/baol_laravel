<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatutCamion extends Model
{
    public $timestamps = false;

    protected $table = 'statut_camions';

    protected $fillable = ['camion_id', 'date', 'statut'];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function camion()
    {
        return $this->belongsTo(Camion::class);
    }

    public const STATUT_CHOICES = [
        'En attente' => 'En attente',
        'En panne' => 'En panne',
        'Travaille pas' => 'Travaille pas',
    ];

    public function getStatutDisplayAttribute(): string
    {
        return self::STATUT_CHOICES[$this->statut] ?? $this->statut;
    }
}
