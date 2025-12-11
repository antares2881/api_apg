<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiurlController extends Controller
{
    public function get(){
        $url = DB::select("SELECT ip_publica FROM apiurls WHERE estado_id = 1");
        return $url;
    }
}
