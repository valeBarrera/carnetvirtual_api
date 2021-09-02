<?php

namespace App\Http\Controllers;

use App\Helpers\GeneratePassword;
use App\Models\Meson;
use Illuminate\Http\Request;
use App\Models\Paciente;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Notifications\ChangePassword;

class PacienteController extends Controller
{
    public function registrar(Request $request)
    {
        $authUser = $request->user();

        if($authUser->rol_id != Meson::ID_MESON){
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        if($authUser->meson->cargo != 'Administrador'){
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
                'run' => 'required|max:8|unique:Paciente,run',
                'nombres' => 'required|max:60',
                'apellidos' => 'required|max:60',
                'fecha_nacimiento' => 'required|date|date_format:d-m-Y|before_or_equal:' . $ahora_str,
                'telefono' => 'required|max:9',
                'username' => 'required|max:16|unique:Usuario,username',
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
                $pass = (new GeneratePassword())->generate();

                $usuario = new Usuario();
                $usuario->email = $request->email;
                $usuario->username = $request->username;
                $usuario->password = Hash::make($pass);
                $usuario->change_password = TRUE;
                $usuario->rol_id = Paciente::ID_PACIENTE;
                $usuario->save();

                $paciente = new Paciente();
                $paciente->run = $request->run;
                $paciente->nombres = $request->nombres;
                $paciente->apellidos = $request->apellidos;
                $paciente->fecha_nacimiento =  \DateTime::createFromFormat("d-m-Y", $request->fecha_nacimiento);
                $paciente->telefono = $request->telefono;
                $paciente->usuario_id = $usuario->id;

                $paciente->save();

                $usuario->notify(new ChangePassword($usuario, $pass));

                $data = new \stdClass();
                $data->code = 200;
                $data->status = 'success';
                $data->mensaje = 'El usuario paciente se ha creado correctamente';

                $data->paciente = $paciente->load(['usuario','usuario.rol']);

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
                'run' => 'required|max:8|exists:Paciente,run',
                'nombres' => 'required|max:60',
                'apellidos' => 'required|max:60',
                'fecha_nacimiento' => 'required|date|date_format:d-m-Y|before_or_equal:' . $ahora_str,
                'telefono' => 'required|max:9',
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
                if($usuario->email != $request->email){
                    $usuario->email = $request->email;
                }

                if($usuario->username != $request->username){
                    $usuario->username = $request->username;
                }

                $usuario->update();

                $paciente = $usuario->paciente;

                if($paciente->run != $request->run){
                    $paciente->run = $request->run;
                }
                if($paciente->nombres != $request->nombres){
                    $paciente->nombres = $request->nombres;
                }
                if($paciente->apellidos != $request->apellidos){
                    $paciente->apellidos = $request->apellidos;
                }
                $paciente->fecha_nacimiento =  \DateTime::createFromFormat("d-m-Y", $request->fecha_nacimiento);
                if($paciente->telefono != $request->telefono){
                    $paciente->telefono = $request->telefono;
                }

                $paciente->update();

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

    public function misCitas(Request $request){
        $authUser = $request->user();

        if ($authUser->rol_id != Paciente::ID_PACIENTE) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Citas obtenidas correctamente';
        $data->citas = $authUser->paciente->citas->load(['medico']);

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }

    public function misMedicamentos(Request $request){
        $authUser = $request->user();

        if ($authUser->rol_id != Paciente::ID_PACIENTE) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Recetas obtenidas correctamente';
        $data->recetas = $authUser->paciente->recetas->load(['medico']);

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }

}
