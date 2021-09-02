<?php

namespace App\Notifications;

use App\Models\Farmacia;
use App\Models\Medico;
use App\Models\Meson;
use App\Models\Paciente;
use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangePassword extends Notification
{
    use Queueable;

    private $usuario;
    private $temporal_pass;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Usuario $user, $pass)
    {
        $this->usuario = $user;
        $this->temporal_pass = $pass;
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
        $data_user = null;
        $is_paciente = false;
        switch ($this->usuario->rol_id) {
            case Meson::ID_MESON:
                $data_user = $this->usuario->meson;
                break;
            case Farmacia::ID_FARMACIA:
                $data_user = $this->usuario->farmacia;
                break;
            case Medico::ID_MEDICO:
                $data_user = $this->usuario->medico;
                break;
            case Paciente::ID_PACIENTE:
                $data_user = $this->usuario->paciente;
                $is_paciente = true;
                break;
        }
        return (new MailMessage)
            ->subject('Cambio de Contraseña')
            ->markdown('mail.change-password', [
                    'url' => 'http://localhost',
                    'nombres' => $data_user->nombres,
                    'apellidos' => $data_user->apellidos,
                    'pass' => $this->temporal_pass,
                    'is_paciente' => $is_paciente,
                    'username' => $this->usuario->username
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
