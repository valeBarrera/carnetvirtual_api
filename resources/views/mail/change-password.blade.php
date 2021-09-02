@component('mail::message')
# Cambio de contraseña

Hola {{$nombres}} {{$apellidos}}, a su cuenta se le ha asignado una nueva contraseña.
@if ($is_paciente)
Su usuario y contraseña se encuentran indicados a continuación:
@else
Es requerido que usted cambie la contraseña temporal que le hemos asignado. Su usuario y contraseña se encuentran indicados a continuación:
@endif


@component('mail::panel')
{{$username}}<br/>
{{$pass}}
@endcomponent

Saludos,<br>
{{ config('app.name') }}
@endcomponent
