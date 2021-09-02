<?php

namespace App\Console\Commands;

use App\Models\Receta;
use App\Notifications\NotifyPhone;
use Illuminate\Console\Command;

class RecetaNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'receta:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envío de notificaciones 1 y 2 días antes del retiro de la receta.';

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
        $recetas = Receta::all();
        foreach ($recetas as $receta) {
            $proximo_retiro = $receta->proximo_retiro;
            if($proximo_retiro != null){
                $fecha1 = date_create(now());
                $fecha2 = date_create($proximo_retiro);
                $intervalo = date_diff($fecha1, $fecha2);
                $dias = $intervalo->format('%a');
                $horas = $intervalo->format('%h');

                if(($dias == 2 && $horas == 0) || ($dias == 1 && $horas > 18) || ($dias == 0 && $horas > 0)) { //2 y 1 dias
                    if ($receta->paciente->token_telefono != null) {
                        $this->info('Notificando Paciente (ID): ' . $receta->paciente->id);
                        $title = 'Recordatorio de Retiro de Receta';
                        $medico = $receta->medico->nombres . ' ' . $receta->medico->apellidos;
                        $body = 'La receta ' . $receta->prescripcion. ' prescrita por ';
                        $body .= ' el Dr. ' . $medico . ' debe ser retirada el día: ';
                        $body .= date("d-m-Y", strtotime($proximo_retiro));
                        $extra_data = ['tipo' => '5'];
                        $receta->paciente->notify(new NotifyPhone($title, $body, $extra_data));
                    }
                }
            }
        }
    }
}
