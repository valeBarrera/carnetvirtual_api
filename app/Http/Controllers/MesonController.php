<?php

namespace App\Http\Controllers;

use App\Helpers\GeneratePassword;
use Illuminate\Http\Request;
use App\Models\Meson;
use Illuminate\Support\Facades\Validator;
use App\Helpers\JwtAuth;
use App\Models\Usuario;
use App\Notifications\ChangePassword;
use Illuminate\Support\Facades\Hash;


class MesonController extends Controller
{
    //

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
                'run' => 'required|max:8|unique:Meson,run',
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
                $data->mensaje = 'El usuario del tipo Mesón no ha podido crearse.';
                $data->errores = $validate->errors();

                return response(json_encode($data), 200)->header('Content-Type', 'application/json');
            } else {

                $pass = (new GeneratePassword())->generate();

                $usuario = new Usuario();
                $usuario->email = $request->email;
                $usuario->username = $request->username;
                $usuario->password = Hash::make($pass);
                $usuario->change_password = TRUE;
                $usuario->rol_id = MESON::ID_MESON;
                $usuario->save();

                $meson = new Meson();
                $meson->run = $request->run;
                $meson->nombres = $request->nombres;
                $meson->apellidos = $request->apellidos;
                $meson->fecha_nacimiento =  \DateTime::createFromFormat("d-m-Y", $request->fecha_nacimiento);
                $meson->telefono = $request->telefono;
                $meson->cargo = $request->cargo;
                $meson->usuario_id = $usuario->id;

                $meson->save();

                $usuario->notify(new ChangePassword($usuario, $pass));

                $data = new \stdClass();
                $data->code = 200;
                $data->status = 'success';
                $data->mensaje = 'El usuario del tipo Mesón se ha creado correctamente';
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
                'run' => 'required|max:8|exists:Meson,run',
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
                $data->mensaje = 'El usuario de paciente no ha podido crearse';
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

                $meson = $usuario->meson;

                if ($meson->run != $request->run) {
                    $meson->run = $request->run;
                }
                if ($meson->nombres != $request->nombres) {
                    $meson->nombres = $request->nombres;
                }
                if ($meson->apellidos != $request->apellidos) {
                    $meson->apellidos = $request->apellidos;
                }
                $meson->fecha_nacimiento =  \DateTime::createFromFormat("d-m-Y", $request->fecha_nacimiento);
                if ($meson->telefono != $request->telefono) {
                    $meson->telefono = $request->telefono;
                }
                if ($meson->cargo != $request->cargo) {
                    $meson->cargo = $request->cargo;
                }

                $meson->save();

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
