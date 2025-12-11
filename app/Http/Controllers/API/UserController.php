<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Auth;
use App\Models\Candidato;

class UserController extends Controller
{

    public function userLogin(Request $request){
        $input = $request->all();
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $validate = \Validator::make($input, [
                'username' => 'required|string',
                'password' => 'required'
            ]);
            if ($validate->fails()) {
                $signup = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha podido identificar',
                    'errors' => $validate->errors()
                );
            }

            if (Auth::attempt(  ['username' => $params_array['username'], 'password' => $params_array['password'], 'estado_id' => 1] )){
                $user = Auth::user();
                
                $id = $user->candidato_id;                
                $candidato = Candidato::where('id', $id)->get();
                $user_id = $user->id;
                $comando = DB::select("SELECT c.id, c.nombre FROM users_comandos as uc INNER JOIN comandos as c ON uc.comando_id = c.id WHERE uc.user_id = $user_id");
                
                $token = $user->createToken('MyApp');
                $signup = array(
                    'status' => 'success',
                    'code' => 200,
                    'token' => $token,
                    'user' => $user,
                    'candidato' => $candidato,
                    'comando' => $comando
                );
            }else{
                $signup = array(
                    'status' => 'incorrect',
                    'code' => 400,
                    'message' => 'Las credenciales son incorrectas',
                );
            }
            return response()->json($signup, 200);

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
