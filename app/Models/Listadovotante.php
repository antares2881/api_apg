<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listadovotante extends Model
{
    use HasFactory;

    public function lider(){
        return $this->belongsTo(Lidere::class, 'lidere_id');
    }

    public function candidato(){
        return $this->belongsTo(Candidato::class, 'candidato_id');
    }

    public function jurado(){
        return $this->hasOne(Jurado::class, 'id');
    }

    public function profesion(){
        return $this->belongsTo(Profesione::class, 'profesione_id');
    }


}
