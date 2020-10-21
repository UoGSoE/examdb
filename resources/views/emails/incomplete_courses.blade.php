@component('mail::message')
# Courses that are not fully approved

This is the list of courses which are not fully approved by moderators :

@foreach ($courses as $course)
{{ $course->code }}
@endforeach

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
