<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Meson;
use App\Models\Paciente;
use App\Models\Usuario;
use App\Notifications\CancelCita;
use App\Notifications\NewCita;
use App\Notifications\NotifyPhone;
use DateTime;
use Illuminate\Support\Facades\Validator;



class CitaController extends Controller
{
    public function obternerCitasById(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON && $authUser->rol_id != Medico::ID_MEDICO) {
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

        if ($user_paciente == null || $user_paciente->rol_id != Paciente::ID_PACIENTE) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'El usuario y/o paciente no existe.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Citas obtenidas exitosamente.';
        $data->paciente = $user_paciente->paciente;

        if ($authUser->rol_id == Medico::ID_MEDICO) {
            $data->citas = $user_paciente->paciente->citas->where('medico_id', $authUser->medico->id)->load(['medico', 'meson']);
        } else {
            $data->citas = $user_paciente->paciente->citas->load(['medico', 'meson']);
        }

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }

    public function obternerCitasByRun(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON && $authUser->rol_id != Medico::ID_MEDICO) {
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

        if ($paciente == null) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'El paciente no existe.';

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Citas obtenidas exitosamente.';
        $data->paciente = $paciente;

        if($authUser->rol_id == Medico::ID_MEDICO){
            $data->citas = $paciente->citas->where('medico_id', $authUser->medico->id)->load(['medico', 'meson']);
        }else{
            $data->citas = $paciente->citas->load(['medico', 'meson']);
        }


        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }

    public function registrarCitas(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON && $authUser->rol_id != Medico::ID_MEDICO) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $validate = Validator::make($request->all(), [
            'fecha' => 'required|string',
            'hora' => 'required|string',
            'estado' => 'required|string',
            'paciente_id' => 'required|numeric|exists:Paciente,id',
            'medico_id' => 'required|numeric|exists:Medico,id'
        ]);

        if ($validate->fails()) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No ha enviado la información correspondiente.';
            $data->errores = $validate->errors();

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $cita = new Cita();
        $cita->fecha = DateTime::createFromFormat("d-m-Y", $request->fecha);
        $cita->hora = $request->hora;
        $cita->estado = $request->estado;
        $cita->paciente_id = $request->paciente_id;
        $cita->medico_id = $request->medico_id;
        $cita->save();

        $paciente = Paciente::find($request->paciente_id);


        $paciente->usuario->notify(new NewCita($cita));

        if ($cita->paciente->token_telefono != null) {
            $title = 'Nueva Cita Médica';
            $body = 'Nueva cita médica con el Dr. ' . $cita->medico->nombres . ' ' . $cita->medico->apellidos . ', ';
            $body .= 'del día ' . $cita->fecha->format('d-m-Y') . ' a las ' . $cita->hora . ' hrs.';
            $extra_data = ['tipo' => '1'];
            $cita->paciente->notify(new NotifyPhone($title, $body, $extra_data));
        }

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Cita registrada exitosamente.';

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }

    public function cancelarCita(Request $request){
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $validate = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:Cita,id'
        ]);

        if ($validate->fails()) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No ha enviado la información correspondiente.';
            $data->errores = $validate->errors();

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $cita = Cita::find($request->id);

        $cita->estado = 'Cancelada';
        $cita->save();

        $cita->paciente->usuario->notify(new CancelCita($cita));

        if ($cita->paciente->token_telefono != null) {
            $title = 'Cita Médica Cancelada';
            $body = 'La cita médica con el Dr. ' . $cita->medico->nombres . ' ' . $cita->medico->apellidos . ', ';
            $body .= 'del día ' . $cita->fecha->format('d-m-Y') . ' a las ' . $cita->hora . ' hrs. a sido Cancelada.';
            $extra_data = ['tipo' => '3'];
            $cita->paciente->notify(new NotifyPhone($title, $body, $extra_data));
        }

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Cita cancelada exitosamente.';
        $data->citas = Cita::where('paciente_id', $cita->paciente_id)->get()->load(['medico', 'meson']);

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }

    public function notificarNuevamente(Request $request){
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $validate = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:Cita,id'
        ]);

        if ($validate->fails()) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No ha enviado la información correspondiente.';
            $data->errores = $validate->errors();

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $cita = Cita::find($request->id);

        if ($cita->paciente->token_telefono != null) {
            $title = 'Nueva Cita Médica';
            $body = 'Nueva cita médica con el Dr. ' . $cita->medico->nombres . ' ' . $cita->medico->apellidos . ', ';
            $body .= 'del día ' . $cita->fecha->format('d-m-Y') . ' a las ' . $cita->hora . ' hrs.';
            $extra_data = ['tipo' => '1'];
            $cita->paciente->notify(new NotifyPhone($title, $body, $extra_data));
        }

        $cita->paciente->usuario->notify(new NewCita($cita));

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Cita notificada exitosamente.';
        $data->citas = Cita::where('paciente_id', $cita->paciente_id)->get()->load(['medico', 'meson']);

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }

    public function obtenerMedicos(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON && $authUser->rol_id != Medico::ID_MEDICO) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $medicos = Medico::all();

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Médicos obtenidos exitosamente.';
        $data->medicos = $medicos;

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
    }
}
