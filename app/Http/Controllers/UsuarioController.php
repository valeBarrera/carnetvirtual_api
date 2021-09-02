<?php

namespace App\Http\Controllers;

use App\Helpers\GeneratePassword;
use App\Models\Farmacia;
use App\Models\Medico;
use App\Models\Meson;
use App\Models\Paciente;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Notifications\ChangePassword;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;



class UsuarioController extends Controller
{

    /** LOGIN */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required_if:email,null|max:16',
            'email' => 'required_if:username,null',
            'password' => 'required|regex:/^[a-zA-Z\d]{6,12}$/',
            'token_telefono' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            $resp = new \stdClass();
            $resp->code = 400;
            $resp->status = 'error';
            $resp->mensaje = 'No cumple con las precondiciones de los campos';
            $resp->errores = $validator->errors();

            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');
        }

        $user = null;

        if(isset($request->username)){
            $user = Usuario::where('username', $request->username)->first();
        }else{
            $user = Usuario::where('email', $request->email)->first();
        }


        if (!$user || !Hash::check($request->password, $user->password)) {
            $resp = new \stdClass();
            $resp->code = 400;
            $resp->status = 'error';
            $resp->mensaje = 'Las credenciales son incorrectas o no existen';
            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');
        }

        $token = $user->createToken('token')->plainTextToken;
        $paciente = $user->paciente;

        if($user->token == NULL){

            if(isset($request->token_telefono) && $user->rol_id == Paciente::ID_PACIENTE){
                $paciente->token_telefono = $request->token_telefono;
                $paciente->save();
            }

            $user->token = $token;
            $user->save();

            if(isset($request->username)){
                $user = Usuario::where('username', $request->username)->first();
            }else{
                $user = Usuario::where('email', $request->email)->first();
            }

            $resp = new \stdClass();
            $resp->code = 200; //OK
            $resp->status = 'éxito';
            $resp->expire_in = 28800;
            $resp->mensaje = 'Sesión iniciada';
            $resp->token = $token;

            if ($user->rol_id == Paciente::ID_PACIENTE){
                $resp->usuario = $user->load(['paciente']);
            }

            if ($user->change_password) {
                $resp->mensaje = 'Sesión iniciada pero debe cambiar contraseña';
                $resp->cambiar_password = TRUE;
            } else {
                $resp->mensaje = 'Sesión iniciada';
                $resp->cambiar_password = FALSE;
            }

            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');

        }else if($user->paciente->token_telefono != NULL) {
            $resp = new \stdClass();
            $resp->code = 400; //OK
            $resp->status = 'error';
            $resp->mensaje = 'Existe otra sesión iniciada';
            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');
        }


    }
    public function refresh(Request $request)
    {
        $id_usuario = $request->user()->id;
        $user = Usuario::find($id_usuario);
        $token = $user->createToken('token')->plainTextToken;

        $user->token = $token;
        $user->save();

        $user = Usuario::find($id_usuario);

        $resp = new \stdClass();
        $resp->code = 200; //OK
        $resp->status = 'éxito';
        $resp->expire_in = 28800;
        $resp->mensaje = 'Token refrescado';
        $resp->token = $token;

        if ($user->rol_id == Meson::ID_MESON) {
            $resp->usuario = $user->load(['rol', 'meson']);
        }

        return response(json_encode($resp), 200)
            ->header('Content-Type', 'application/json');

    }

    public function changePassword(Request $request) {

        $validator = Validator::make($request->all(), [
            'password' => 'required|regex:/^[a-zA-Z\d]{6,12}$/',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            $resp = new \stdClass();
            $resp->code = 400;
            $resp->status = 'error';
            $resp->mensaje = 'No cumple con las precondiciones de los campos';
            $resp->errores = $validator->errors();

            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');
        }

        $user = $request->user();

        $user->password = Hash::make($request->password);
        $user->change_password = FALSE;
        $user->save();

        $resp = new \stdClass();
        $resp->code = 200; //OK
        $resp->status = "éxito";
        $resp->mensaje = 'Cambio de contraseña exitoso.';

        return response(json_encode($resp), 200)
            ->header('Content-Type', 'application/json');
    }

    public function resetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            $resp = new \stdClass();
            $resp->code = 400;
            $resp->status = 'error';
            $resp->mensaje = 'No cumple con las precondiciones de los campos';
            $resp->errores = $validator->errors();

            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');
        }

        $user = Usuario::where('id', $request->id)->first();

        $pass = (new GeneratePassword())->generate();
        $user->password = Hash::make($pass);
        $user->change_password = TRUE;
        $user->save();

        $user->notify(new ChangePassword($user, $pass));

        $resp = new \stdClass();
        $resp->code = 200; //OK
        $resp->status = "éxito";
        $resp->mensaje = 'Contraseña exitosamente restablecida.';
        return response(json_encode($resp), 200)
            ->header('Content-Type', 'application/json');

    }

    public function login_web(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|regex:/^[a-zA-Z\d]{6,12}$/',
        ]);

        if ($validator->fails()) {
            $resp = new \stdClass();
            $resp->code = 400;
            $resp->status = 'error';
            $resp->mensaje = 'No cumple con las precondiciones de los campos';
            $resp->errores = $validator->errors();

            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');
        }

        $user = Usuario::where('username', $request->username)->first();


        if (!$user || !Hash::check($request->password, $user->password)) {
            $resp = new \stdClass();
            $resp->code = 400;
            $resp->status = 'error';
            $resp->mensaje = 'Las credenciales son incorrectas o no existen';
            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');
        }

        if ($user->rol_id == Paciente::ID_PACIENTE){
            $resp = new \stdClass();
            $resp->code = 400;
            $resp->status = 'error';
            $resp->mensaje = 'El rol de paciente no tiene autorización para ingresar.';
            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');
        }

        if($user->token != NULL){
            $resp = new \stdClass();
            $resp->code = 400;
            $resp->status = 'error';
            $resp->mensaje = 'El usuario tiene un sesión iniciada.';
            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');
        }

        $token = $user->createToken('token')->plainTextToken;

        if ($user->token == NULL) {

            $user->token = $token;
            $user->save();

            $user = Usuario::where('username', $request->username)->first();

            $resp = new \stdClass();
            $resp->code = 200; //OK
            $resp->status = 'éxito';
            $resp->expire_in = 3600;
            $resp->token = $token;

            if ($user->rol_id == Meson::ID_MESON) {
                $resp->usuario = $user->load(['rol', 'meson']);
            } else if ($user->rol_id == Medico::ID_MEDICO) {
                $resp->usuario = $user->load(['rol', 'medico']);
            } else if ($user->rol_id == Farmacia::ID_FARMACIA) {
                $resp->usuario = $user->load(['rol', 'farmacia']);
            }

            if ($user->change_password) {
                $resp->mensaje = 'Sesión iniciada pero debe cambiar contraseña';
                $resp->cambiar_password = TRUE;
            }else{
                $resp->mensaje = 'Sesión iniciada';
                $resp->cambiar_password = FALSE;
            }

            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');
        }
    }

    public function getUser(Request $request) {
        $user = $request->user();
        if($user == NULL){
            $resp = new \stdClass();
            $resp->code = 400; //OK
            $resp->status = "error";
            $resp->mensaje = 'Usuario no autenticado';
            return response(json_encode($resp), 200)
                ->header('Content-Type', 'application/json');
        }
        $resp = new \stdClass();
        $resp->code = 200; //OK
        $resp->status = "éxito";
        $resp->mensaje = 'Usuario obtenido';
        $resp->usuario = $user->load(['rol']);;
        return response(json_encode($resp), 200)
            ->header('Content-Type', 'application/json');
    }


    public function logout(Request $request)
    {
        $user = $request->user();

        if($user->rol_id == Paciente::ID_PACIENTE){
            $paciente = $user->paciente;
            $paciente->token_telefono = NULL;
            $paciente->save();
        }

        $request->user()->currentAccessToken()->delete();

        $user->token = NULL;
        $user->save();

        $resp = new \stdClass();
        $resp->code = 200; //OK
        $resp->status = "éxito";
        $resp->mensaje = 'Sesión cerrada';
        return response(json_encode($resp), 200)
            ->header('Content-Type', 'application/json');
    }

    public function all(Request $request){
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $resp = new \stdClass();
        $resp->code = 200; //OK
        $resp->status = 'éxito';
        $resp->mensaje = 'Lista de usuarios obtenida exitosamente.';

        $resp->usuarios = Usuario::all()->load(['rol', 'farmacia', 'meson', 'medico', 'paciente']);

        return response(json_encode($resp), 200)
            ->header('Content-Type', 'application/json');
    }
}
