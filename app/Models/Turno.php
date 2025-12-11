<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;
    public function agenda(){
        return $this->hasOne(Agenda::class, 'turno_id');
    }
}
