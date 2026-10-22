<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    public $timestamps = false;

    protected $fillable = ['mois', 'fichier'];

    protected $casts = [
        'mois' => 'date:Y-m-d',
        'date_ajout' => 'datetime',
    ];

    public function getMoisDisplayAttribute(): string
    {
        return $this->mois ? $this->mois->format('F Y') : '';
    }
}
