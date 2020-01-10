@component('mail::message')
# Exam Papers for course {{ $course->code }}

The School of Engineering Teaching Office wants you to know there is updated
paperwork for course {{ $course->code }}.  You can log into the system using the link
below.

@component('mail::button', ['url' => route('home')])
Exam Paper Database
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
