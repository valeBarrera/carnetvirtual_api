@component('mail::message')
# Cita Médica Cancelada

Hola {{$nombres}} {{$apellidos}}, lamentablemente le informamos que su cita médica con los siguientes datos, ha sido <b>Cancelada</b>:

@component('mail::panel')
<b>Médico:</b> {{$medico_nombres}} {{$medico_apellidos}}<br/>
<b>Especialidad:</b> {{$medico_especialidad}}<br/>
<b>Fecha y Hora:</b> {{date('d-m-Y', strtotime($fecha))}} {{$hora}}
@endcomponent

Saludos,<br>
{{ config('app.name') }}
@endcomponent
