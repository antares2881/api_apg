<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divipoletestigo extends Model
{
    use HasFactory;

    public function testigo(){
        return $this->hasMany(Testigo::class, 'divipoletestigo_id');
    }

    public function partido()
    {
        return $this->hasOneThrough(
            Partido::class,
            Testigo::class,
            'id', // Foreign key on the cars table...
            'id', // Foreign key on the owners table...
            'partido_id', // Local key on the mechanics table...
            'divipoletestigo_id' // Local key on the cars table...
        );
    }

}
