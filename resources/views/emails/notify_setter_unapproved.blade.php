@component('mail::message')
# {{ ucfirst($category) }} Paper Unapproved

The {{ ucfirst($category) }} paper for course {{ $course->code }} has been unapproved
by the moderator.  You can view the papers by clicking the link below.

@component('mail::button', ['url' => route('course.show', $course)])
Papers for {{ $course->code }}
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
