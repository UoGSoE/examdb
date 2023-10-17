@component('mail::message')
# Exam paper submission deadline has passed

Dear exam paper question setter, your exam paper is overdue and will impede the timely execution of our exam paper schedule, including moderation.  A short in person meeting will be scheduled with the Head of School and appropriate Head of Discipline to understand the delay.

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
