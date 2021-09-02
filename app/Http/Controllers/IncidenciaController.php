<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;
use App\Models\Incidencia;
use App\Models\Medico;
use App\Models\Meson;
use App\Notifications\NotifyPhone;
use DateTime;
use Illuminate\Support\Facades\Validator;



class IncidenciaController extends Controller
{
   public function obtenerIncidencias(Request $request){
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Inicidencia obtenidas exitosamente.';
        $data->incidencias = Incidencia::all()->load(['citas.medico']);

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');

   }

    public function obtenerMedicos(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON) {
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

   public function registrarIncidencias(Request $request){
        $authUser = $request->user();

        if ($authUser->rol_id != Meson::ID_MESON) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No tiene el rol necesario para realizar esta operación.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $validate = Validator::make($request->all(), [
            'fecha' => 'required|string',
            'descripcion' => 'required|string',
            'tipo' => 'required|string',
            'medico_id' => 'required|integer|exists:Medico,id'
        ]);

        if ($validate->fails()) {
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'No ha enviado la información correspondiente.';
            $data->errores = $validate->errors();

            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $medico = Medico::find($request->medico_id);
        $citas = Cita::where('medico_id', $medico->id)->where('fecha', $request->fecha)->get();

        if($citas->count() == 0){
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'El médico no tiene citas en el día indicado.';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $cant_citas_no_canceladas = 0;

        foreach ($citas as $cita) {
            if($cita->estado == 'Activa'){
                $cant_citas_no_canceladas++;
            }
        }

        if($cant_citas_no_canceladas == 0){
            $data = new \stdClass();
            $data->code = 400;
            $data->status = 'error';
            $data->mensaje = 'Ya se ha registrado esta incidencia, no hay citas afectadas';
            return response(json_encode($data), 200)->header('Content-Type', 'application/json');
        }

        $incidencia = new Incidencia();
        $incidencia->descripcion = $request->descripcion;
        $incidencia->tipo = $request->tipo;
        $incidencia->fecha = DateTime::createFromFormat("Y-m-d", $request->fecha);

        $incidencia->save();

        foreach ($citas as $cita) {
            if($cita->estado == 'Activa'){
                $cita->estado = 'Cancelada por Incidencia';
                $cita->save();
                $incidencia->citas()->save($cita);
                if ($cita->paciente->token_telefono != null) {
                    $title = 'Cita Médica Cancelada por Incidencia';
                    $body = 'La cita médica con el Dr. ' . $cita->medico->nombres . ' ' . $cita->medico->apellidos . ', ';
                    $body .= 'del día ' . date('d-m-Y', strtotime($cita->fecha)) . ' a las ' . $cita->hora . ' hrs. a sido Cancelada debido a una ';
                    $body .= 'incidencia en el servicio.';
                    $extra_data = ['tipo' => '2'];
                    $cita->paciente->notify(new NotifyPhone($title, $body, $extra_data));
                }
            }
        }

        $data = new \stdClass();
        $data->code = 200;
        $data->status = 'success';
        $data->mensaje = 'Incidencia registrada exitosamente.';

        return response(json_encode($data), 200)->header('Content-Type', 'application/json');
   }
}
