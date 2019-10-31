@component('mail::message')
# Exam Papers

The paperwork for some courses you set/moderate is still incomplete.  Please log in to the system and have a look.
@component('mail::button', ['url' => route('home')])
Exam Database
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
