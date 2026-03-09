<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Candidato;

class UserController extends Controller
{

    public function userLogin(Request $request){
        $input = $request->all();

        // Backward compatibility: old clients send credentials inside "json" string.
        if ($request->filled('json')) {
            $paramsArray = json_decode($request->input('json'), true);
            if (is_array($paramsArray)) {
                $input = array_merge($input, $paramsArray);
            }
        }

        $validate = \Validator::make($input, [
            'username' => 'required|string',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'El usuario no se ha podido identificar',
                'errors' => $validate->errors()
            ], 422);
        }

        if (Auth::attempt([
            'username' => $input['username'],
            'password' => $input['password'],
            'estado_id' => 1
        ])) {
            $user = Auth::user();

            $id = $user->candidato_id;
            $candidato = Candidato::where('id', $id)->get();
            $user_id = $user->id;
            $comando = DB::select("SELECT c.id, c.nombre FROM users_comandos as uc INNER JOIN comandos as c ON uc.comando_id = c.id WHERE uc.user_id = $user_id");

            $token = $user->createToken('MyApp');

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'token' => $token,
                'user' => $user,
                'candidato' => $candidato,
                'comando' => $comando
            ], 200);
        }

        return response()->json([
            'status' => 'incorrect',
            'code' => 401,
            'message' => 'Las credenciales son incorrectas',
        ], 401);

    }

    public function userDecoded(){
        $user = Auth::guard('api')->user();
        return response()->json(['data' => $user], 200);
    }

    public function destroySession(Request $request){
        $token = DB::select("UPDATE oauth_access_tokens SET revoked = 1 WHERE id = '".$request->id." '");
        return response()->json($token, 200);
    }
}
