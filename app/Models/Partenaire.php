<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partenaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'email',
        'password',
        'tupe_service',
        'contact',
        'admin_id'
    ];
    public function voyages()
    {
        return $this->hasMany(Voyage::class);
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
