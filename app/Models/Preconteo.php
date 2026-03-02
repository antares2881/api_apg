<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preconteo extends Model
{
    use HasFactory;

    public function divipolepreconteo(){
        return $this->belongsTo(Divipolepreconteo::class, 'divipolepreconteo_id');
    }

    public function preconteo_votaciones(){
        return $this->hasMany(Preconteo_votacione::class, 'preconteo_id');
    }

    public function preconteo_observaciones(){
        return $this->hasMany(Preconteo_observacione::class, 'preconteo_id');
    }
}
