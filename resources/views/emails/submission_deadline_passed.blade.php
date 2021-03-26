@component('mail::message')
# Exam paper submission deadline has passed

Dear exam paper question setter,  This is a reminder to upload your exam paper for the upcoming
exam diet and the resit paper by {{ $deadline->format('d/m/Y') }}. Please remember to complete and upload the checklist
as without the checklist, the moderation process will not be triggered

@if (count($courses))
@component('mail::panel')
The following courses need attention :
@foreach ($courses as $code)
* {{ $code }}
@endforeach
@endcomponent
@endif

@component('mail::button', ['url' => '/'])
Log In
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
