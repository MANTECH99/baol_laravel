<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Camion extends Model
{
    public $timestamps = false;

    protected $fillable = ['numero', 'telephone'];

    public function livraisons()
    {
        return $this->hasMany(Livraison::class);
    }

    public function statuts()
    {
        return $this->hasMany(StatutCamion::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return "Camion {$this->numero} - {$this->telephone}";
    }
}
