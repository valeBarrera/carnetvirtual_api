<?php

namespace App\Http\Controllers;

use App\Models\Farmacia;
use App\Models\Medico;
use App\Models\Meson;
use App\Models\Paciente;
use Illuminate\Http\Request;
use App\Models\Receta;
use App\Models\Usuario;
use App\Notifications\NotifyPhone;
use DateTime;
use Illuminate\Support\Facades\Validator;

class RecetaController extends Controller
{

    public function obternerRecetasById(Request $request){
        $authUser = $request->user();

        if ($authUser->rol_id != Farmacia::ID_FARMACIA && $authUser->rol_id != Medico::ID_MEDICO) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $validate = Validator::make($request->all(), [
            'id_usuario' => 'required|integer'
        ]);

        if ($validate->fails()) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No ha enviado la información correspondiente.';
            $data->errores = $validate->errors();

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $id_usuario = $request->id_usuario;

        $user_paciente = Usuario::find($id_usuario);

        if($user_paciente == null || $user_paciente->rol_id != Paciente::ID_PACIENTE){
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'El usuario y/o paciente no existe.';

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Recetas obtenidas exitosamente.';
        $data->paciente = $user_paciente->paciente;
        $data->recetas = $user_paciente->paciente->recetas->load('medico','farmacia');

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');

    }

    public function obternerRecetasByRun(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->rol_id != Farmacia::ID_FARMACIA && $authUser->rol_id != Medico::ID_MEDICO) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $validate = Validator::make($request->all(), [
            'run' => 'required|integer'
        ]);

        if ($validate->fails()) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No ha enviado la información correspondiente.';
            $data->errores = $validate->errors();

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $run = $request->run;

        $paciente = Paciente::where('run', $run)->first();

        if ($paciente == null ) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'El paciente no existe.';

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Recetas obtenidas exitosamente.';
        $data->paciente = $paciente;
        $data->recetas = $paciente->recetas->load(['medico', 'farmacia']);

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }

    public function registrarRecetas(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->rol_id != Medico::ID_MEDICO) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $validate = Validator::make($request->all(), [
            'paciente_id' => 'required|integer|exists:Paciente,id',
            'indicaciones' => 'required|string',
            'prescripcion' => 'required|string',
            'estado' => 'required|string'
        ]);

        if ($validate->fails()) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No ha enviado la información correspondiente.';
            $data->errores = $validate->errors();

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $receta = new Receta();
        $receta->indicaciones = $request->indicaciones;
        $receta->prescripcion = $request->prescripcion;
        $receta->estado = $request->estado;
        $receta->fecha_emision = new DateTime();
        $receta->paciente_id = $request->paciente_id;
        $receta->medico_id = $authUser->medico->id;
        $receta->save();

        if ($receta->paciente->token_telefono != null) {
            $title = 'Nueva Receta Asignada';
            $body = 'El Dr. ' . $receta->medico->nombres . ' ' . $receta->medico->apellidos . ' le ha asignado la siguiente receta: ';
            $body .= $receta->prescripcion;
            $extra_data = ['tipo' => '4'];
            $receta->paciente->notify(new NotifyPhone($title, $body, $extra_data));
        }

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Receta registrada exitosamente.';

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }

    public function surtirRecetas(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->rol_id != Farmacia::ID_FARMACIA) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $validate = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'proximo_retiro'=> 'required|string'
        ]);

        if ($validate->fails()) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No ha enviado la información correspondiente.';
            $data->errores = $validate->errors();

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $receta = Receta::find($request->id);
        if ($receta == null) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No existe la receta.';

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $receta->ultima_fecha_surtido = new DateTime();
        $receta->farmacia_id = $authUser->farmacia->id;
        $receta->proximo_retiro = DateTime::createFromFormat("d-m-Y", $request->proximo_retiro);
        $receta->save();

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Receta surtida exitosamente.';
        $data->recetas = $receta->paciente->recetas->load(['farmacia', 'medico']);

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }

    public function obtenerRecetasPaciente(Request $request){
        $authUser = $request->user();

        if ($authUser->rol_id != Paciente::ID_PACIENTE) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $paciente = $authUser->paciente;

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Recetas obtenidas exitosamente.';
        $data->recetas = $paciente->recetas->load(['medico', 'farmacia']);

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }

    public function modificarRecetas(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->rol_id != Medico::ID_MEDICO) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $validate = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'indicaciones' => 'required|string',
            'prescripcion' => 'required|string',
            'estado' => 'required|string'
        ]);

        if ($validate->fails()) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No ha enviado la información correspondiente.';
            $data->errores = $validate->errors();

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $receta = Receta::find($request->id);

        if($receta == null){
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No existe la receta.';

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        if($receta->indicaciones != $request->indicaciones){
            $receta->indicaciones = $request->indicaciones;
        }
        if($receta->prescripcion != $request->prescripcion){
            $receta->prescripcion = $request->prescripcion;
        }

        if($receta->estado != $request->estado){
            $receta->estado = $request->estado;
        }

        $receta->save();

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Receta modificada exitosamente.';
        $data->recetas = $receta->paciente->recetas->load(['farmacia', 'medico']);

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }
}
