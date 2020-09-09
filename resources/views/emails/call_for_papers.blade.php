@component('mail::message')
# Exams: Call for papers

Some text

@component('mail::button', ['url' => route('/')])
Log in here
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
