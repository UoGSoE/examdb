@component('mail::message')
# {{ ucfirst($category) }} Paper Approved

The {{ ucfirst($category) }} paper for course {{ $course->code }} has been approved
by the {{ $userType }}. You can view the papers by clicking the link below.

@component('mail::button', ['url' => route('course.show', $course->id)])
Papers for {{ $course->code }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent