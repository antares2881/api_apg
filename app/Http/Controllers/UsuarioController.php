<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsuarioController extends Controller
{
    public function index(){
        $users = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('estados', 'users.estado_id', '=', 'estados.id')
            ->leftJoin('candidatos', 'users.candidato_id', '=', 'candidatos.id')
            ->select('users.*', 'roles.role', 'candidatos.nombres as nombre_candidato', 'roles.id as role_id', 'estados.estado')
            ->orderBy('nombre_candidato', 'DESC')
            ->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'users' => $users
        );
        return response()->json($data, $data['code']);
    }

    public function show($corporacion){

        $condicion = "";
        if($corporacion == 5){
            $id = Auth::user()->candidato_id;
            $users = DB::table('users')
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->join('estados', 'users.estado_id', '=', 'estados.id')
                ->Join('candidatos', 'users.candidato_id', '=', 'candidatos.id')
                ->select('users.*', 'roles.role', 'candidatos.nombres as nombre_candidato', 'roles.id as role_id', 'estados.estado')     
                ->where('users.candidato_id', $id)
                ->orderBy('nombre_candidato', 'DESC')
                ->get();
            
        }else{
            $users = DB::table('users')
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->join('estados', 'users.estado_id', '=', 'estados.id')
                ->Join('candidatos', 'users.candidato_id', '=', 'candidatos.id')
                ->leftJoin('users_comandos', 'users.id', '=', 'users_comandos.user_id')
                ->select('users.*', 'roles.role', 'candidatos.nombres as nombre_candidato', 'roles.id as role_id', 'estados.estado', 'users_comandos.comando_id') 
                ->orderBy('nombre_candidato', 'DESC')
                ->get();
        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'users' => $users
        );
        return response()->json($data, $data['code']);
    }

    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required',
            'role_id' => 'required',
            'candidato_id' => 'required',
            'password' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $user = new User();
        if($request->role_id == 5 || $request->role_id == 6 || $request->role_id == 8){
            $user->id = $request->id;
        }
        $user->role_id = $request->role_id;
        $user->name = $request->name;
        $user->username = $request->username;
        $user->candidato_id = $request->candidato_id;
        $user->estado_id = 1;
        $user->password = Hash::make($request->password);
        $user->save();

        $comando = DB::insert('insert into users_comandos (user_id, comando_id) values (?, ?)', [$user->id, $request->comando_id]);

        $new_user = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('estados', 'users.estado_id', '=', 'estados.id')
            ->leftJoin('candidatos', 'users.candidato_id', '=', 'candidatos.id')
            ->select('users.*', 'roles.role', 'candidatos.nombres as nombre_candidato', 'roles.id as role_id', 'estados.estado')
            ->where('users.id', $user->id)
            ->get();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'user' => $new_user
        );
        return response()->json($data, $data['code']);
    }

    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required',
            'role_id' => 'required',
            'estado_id' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $user = User::find($id);
        $user->role_id = $request->role_id;
        $user->name = $request->name;
        $user->username = $request->username;
        $user->estado_id = $request->estado_id;
        $user->save();

        $new_user = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('estados', 'users.estado_id', '=', 'estados.id')
            ->leftJoin('candidatos', 'users.candidato_id', '=', 'candidatos.id')
            ->select('users.*', 'roles.role', 'candidatos.nombres as nombre_candidato', 'roles.id as role_id', 'estados.estado')
            ->where('users.id', $id)
            ->get();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'user' => $new_user
        );
        return response()->json($data, $data['code']);
    }

    public function change_password(Request $request){
        $validator = \Validator::make($request->all(), [
            'password' => 'min:6'
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $user = User::where('username', $request->username)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'user' => $user
        );
        return response()->json($data, $data['code']);
    }
}
