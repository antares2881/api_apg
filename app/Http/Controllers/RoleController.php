<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function index(){
        $roles = Role::all();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'roles' => $roles
        );
        return response()->json($data, $data['code']);
    }
}
