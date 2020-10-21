@component('mail::message')
# ExamDB

The externals deadline for {{ $area }} is up.  Please log into the exam paper database
and check things look ok.  Then you can trigger the notifications to external examiners.

@component('mail::button', ['url' => route('home')])
Exam Database
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
