@component('mail::message')
# Nueva Cita Médica

Hola {{$nombres}} {{$apellidos}}, le informamos que tiene una nueva cita médica:

@component('mail::panel')
<b>Médico:</b> {{$medico_nombres}} {{$medico_apellidos}}<br/>
<b>Especialidad:</b> {{$medico_especialidad}}<br/>
<b>Fecha y Hora:</b> {{$fecha->format('d-m-Y')}} {{$hora}}
@endcomponent


Saludos,<br>
{{ config('app.name') }}
@endcomponent
