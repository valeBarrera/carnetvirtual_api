<?php

namespace App\Notifications;

use App\Models\Cita;
use App\Models\Paciente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCita extends Notification
{
    use Queueable;
    private $cita;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nueva Cita Médica')
            ->markdown('mail.new-cita', [
                    'nombres' => $this->cita->paciente->nombres,
                    'apellidos' => $this->cita->paciente->apellidos,
                    'hora' => $this->cita->hora,
                    'fecha' => $this->cita->fecha,
                    'medico_nombres' => $this->cita->medico->nombres,
                    'medico_apellidos' => $this->cita->medico->apellidos,
                    'medico_especialidad' => $this->cita->medico->especialidad,
                ]
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
