<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ipreporte;

class IpreporteController extends Controller
{
    public function index(){
        $ips = DB::select("SELECT ip.*, u.name, u.username
            FROM ipreportes AS ip 
            INNER JOIN users as u ON ip.user_id = u.id
            ORDER BY ip.created_at DESC");
        $data = array(
            "status" => 'success',
            "code" => 200,
            "ips" => $ips
        );
        return response()->json($data, $data['code']);
    }
}
