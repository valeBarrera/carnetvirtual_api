<?php

namespace App\Http\Controllers;

use App\Helpers\GeneratePassword;
use Illuminate\Http\Request;
use App\Models\Farmacia;
use Illuminate\Support\Facades\Validator;
use App\Helpers\JwtAuth;
use App\Models\Meson;
use App\Models\Usuario;
use App\Notifications\ChangePassword;
use Illuminate\Support\Facades\Hash;





class FarmaciaController extends Controller
{
    public function registrar(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        if ($authUser->meson->cargo != 'Administrador') {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'Su usuario del tipo Méson no tiene el cargo necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $ahora = now();
        $ahora_str = $ahora->format("d-m-Y");

        //validacion
        if (!empty($request->all())) {
            $validate = Validator::make($request->all(), [
                'run' => 'required|max:8|unique:Farmacia,run',
                'nombres' => 'required|max:60',
                'apellidos' => 'required|max:60',
                'fecha_nacimiento' => 'required|date|date_format:d-m-Y|before_or_equal:' . $ahora_str,
                'telefono' => 'required|max:9',
                'cargo' => 'required|max:20',
                'username' => 'required|max:16|unique:Usuario,username',
                'email' => 'required|email:rfc,dns',
            ]);
            if ($validate->fails()) {
                $data = new \stdClass();
                $data->code = 400;
                $data->status = 'error';
                $data->mensaje = 'El usuario del tipo Farmacia no ha podido crearse.';
                $data->errores = $validate->errors();

                return response(json_encode($data), 200)->header('Content-Type', 'application/json');
            } else {

                $pass = (new GeneratePassword())->generate();

                $usuario = new Usuario();
                $usuario->email = $request->email;
                $usuario->username = $request->username;
                $usuario->password = Hash::make($pass);
                $usuario->change_password = TRUE;
                $usuario->rol_id = Farmacia::ID_FARMACIA;
                $usuario->save();

                $farmacia = new Farmacia();
                $farmacia->run = $request->run;
                $farmacia->nombres = $request->nombres;
                $farmacia->apellidos = $request->apellidos;
                $farmacia->fecha_nacimiento =  \DateTime::createFromFormat("d-m-Y", $request->fecha_nacimiento);
                $farmacia->telefono = $request->telefono;
                $farmacia->cargo = $request->cargo;
                $farmacia->usuario_id = $usuario->id;

                $farmacia->save();

                $usuario->notify(new ChangePassword($usuario, $pass));

                $data = new \stdClass();
                $data->code = 200;
                $data->status = 'success';
                $data->mensaje = 'El usuario del tipo Farmacia se ha creado correctamente';
                return response(json_encode($data), 200)->header('Content-Type', 'application/json');
            }
        } else {
            $data = [
                'code' => 200,
                'status' => 'error',
                'mensaje' => 'No ha ingresado los parámetros correctos'
            ];

            $data = new \stdClass();
            $data->code = 200;
            $data->status = 'error';
            $data->mensaje = 'No ha ingresado los parámetros correctos';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }
    }

    public function modificar(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $ahora = now();
        $ahora_str = $ahora->format("d-m-Y");

        //validacion
        if (!empty($request->all())) {
            $validate = Validator::make($request->all(), [
                'id' => 'required|max:8|exists:Usuario,id',
                'run' => 'required|max:8|exists:Farmacia,run',
                'nombres' => 'required|max:60',
                'apellidos' => 'required|max:60',
                'fecha_nacimiento' => 'required|date|date_format:d-m-Y|before_or_equal:' . $ahora_str,
                'telefono' => 'required|max:9',
                'cargo' => 'required|max:20',
                'username' => 'required|max:16',
                'email' => 'required|email:rfc,dns',
                /*'password' => 'bail|required|regex:/^[a-zA-Z\d]{6,12}$/',*/
            ]);
            if ($validate->fails()) {
                $data = new \stdClass();
                $data->code = 400;
                $data->status = 'error';
                $data->mensaje = 'El usuario de farmacia no ha podido crearse';
                $data->errores = $validate->errors();
                return response(json_encode($data), 200)->header('Content-Type', 'application/json');
            } else {


                $usuario = Usuario::find($request->id);
                if ($usuario->email != $request->email) {
                    $usuario->email = $request->email;
                }

                if ($usuario->username != $request->username) {
                    $usuario->username = $request->username;
                }

                $usuario->save();

                $farmacia = $usuario->farmacia;

                if ($farmacia->run != $request->run) {
                    $farmacia->run = $request->run;
                }
                if ($farmacia->nombres != $request->nombres) {
                    $farmacia->nombres = $request->nombres;
                }
                if ($farmacia->apellidos != $request->apellidos) {
                    $farmacia->apellidos = $request->apellidos;
                }
                $farmacia->fecha_nacimiento =  \DateTime::createFromFormat("d-m-Y", $request->fecha_nacimiento);
                if ($farmacia->telefono != $request->telefono) {
                    $farmacia->telefono = $request->telefono;
                }
                if ($farmacia->cargo != $request->cargo) {
                    $farmacia->cargo = $request->cargo;
                }

                $farmacia->save();

                $data = new \stdClass();
                $data->code = 200;
                $data->status = 'success';
                $data->mensaje = 'El usuario paciente se ha modificado correctamente';
                $data->usuarios = Usuario::all()->load(['rol', 'farmacia', 'meson', 'medico', 'paciente']);

                return response(json_encode($data), 200)->header('Content-Type', 'application/json');
            }
        } else {
            $data = [
                'code' => 200,
                'status' => 'error',
                'mensaje' => 'No ha ingresado los parámetros correctos'
            ];

            $data = new \stdClass();
            $data->code = 200;
            $data->status = 'error';
            $data->mensaje = 'No ha ingresado los parámetros correctos';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }
    }
}
