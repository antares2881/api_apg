<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lidere extends Model
{
    use HasFactory;

    public function coordinador(){
        return $this->belongsTo(Coordinadore::class, 'coordinadore_id');
    }
    public function candidato(){
        return $this->belongsTo(Candidato::class, 'candidato_id');
    }
}
