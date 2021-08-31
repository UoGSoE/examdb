@component('mail::message')
# Data has been copied to new academic session

All of the data has now been copied to the new academic session _{{ $session->session }}_

Thanks,<br>
{{ config('app.name') }}
@endcomponent
