@component('mail::message')
# Moderation deadline has passed.

Dear exam paper moderator, your exam paper moderation is overdue and will impede the timely execution of our exam paper schedule, including External Examiner review.  A short in person meeting will be scheduled with the Head of School and appropriate Head of Discipline to understand the delay.

@if (count($courses))
@component('mail::panel')
The following courses need attention :
@foreach ($courses as $code)
* {{ $code }}
@endforeach
@endcomponent
@endif

@component('mail::button', ['url' => '/'])
Log in
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
