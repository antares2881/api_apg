<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Candidato;

class Candidato extends Model
{
    use HasFactory;

    public function corporacion(){
        return $this->belongsTo(Corporacione::class, 'corporacione_id');
    }

    public function partido(){
        return $this->belongsTo(Partido::class, 'partido_id');
    }
}
