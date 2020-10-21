@component('mail::message')
# {{ ucfirst($category) }} Paper Approved

The {{ ucfirst($category) }} paper for course {{ $course->code }} has been approved
by the setter.  You can view the papers by clicking the link below.

@component('mail::button', ['url' => route('course.show', $course->id)])
Papers for {{ $course->code }}
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
