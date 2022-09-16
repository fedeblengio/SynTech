@component('mail::message')

    # Tareas LMS
    ### Se le informa que el profesor {{ $details['nombreUsuario'] }} a subido una tarea de {{ $details['nombreMateria'] }} para el grupo **{{ $details['grupo'] }}**


    Consejo : Recuerda que tienes un tiempo limitado para entregar esta tarea , no dejes todo para ultimo momento.

    @component('mail::button', ['url' => 'http://localhost:8080/'])
        Ir al Sitio
    @endcomponent
  
@endcomponent