<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Divipolepreconteo extends Model
{
    use HasFactory;

    public function preconteo(){
        
        return $this->hasMany(Preconteo::class, 'divipolepreconteo_id');
    }
}
