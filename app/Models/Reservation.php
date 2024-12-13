<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_reservation',
        'statut',
        'voyage_id',
        'client_id'
    ];

    public function voyage()
    {
        return $this->belongsTo(Voyage::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
