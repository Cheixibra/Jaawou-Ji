<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voyage extends Model
{
    use HasFactory;
    protected $fillable = [
        'destination',
        'date_depart',
        'prix',
        'disponibilite',
        'partenaire_id'
    ];

    //Relation avec le modèle Partenaire
    public function partenaire()
    {
        return $this->belongsTo(Partenaire::class);
    }
}
