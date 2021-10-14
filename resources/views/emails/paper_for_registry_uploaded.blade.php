@component('mail::message')
# Paper for Registry

The Paper for Registry has been uploaded for course {{ $course->code }}.  Please log in
and approve it using the link below.

@component('mail::button', ['url' => route('course.show', $course->id)])
Log In
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
