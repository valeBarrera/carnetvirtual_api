<?php

namespace App\Console\Commands;

use App\Models\Cita;
use App\Notifications\NotifyPhone;
use Illuminate\Console\Command;

class CitaNotification extends Command
{
    private $ESTADO_ACTIVO = 'Activa';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cita:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envío de notificaciones 1 y 2 días antes de la cita médica.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $citas = Cita::all();
        foreach ($citas as $cita) {
            if($cita->estado == $this->ESTADO_ACTIVO){
                $fecha1 = date_create(now());
                $fecha2 = date_create($cita->fecha);
                $intervalo = date_diff($fecha1, $fecha2);
                $dias = $intervalo->format('%a');
                $horas = $intervalo->format('%h');

                if (($dias == 2 && $horas == 0) || ($dias == 1 && $horas > 18) || ($dias == 0 && $horas > 0)) { //2 y 1 dias
                    if ($cita->paciente->token_telefono != null) {
                        $this->info('Notificando Paciente (ID): ' . $cita->paciente->id);
                        $title = 'Recordario de Cita Médica';
                        $body = 'Recordar que tiene cita con el Dr. ' . $cita->medico->nombres . ' ' . $cita->medico->apellidos . ', ';
                        $body .= 'el día ' . date("d-m-Y", strtotime($cita->fecha)) . ' a las ' . $cita->hora . ' hrs.';
                        $extra_data = ['tipo' => '6'];
                        $cita->paciente->notify(new NotifyPhone($title, $body, $extra_data));
                    }
                }
            }
        }
    }
}
