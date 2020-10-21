@component('mail::message')
# External Comments

The external for {{ $course->code }} has uploaded their comments.

@component('mail::button', ['url' => route('course.show', $course->id)])
{{ $course->code }}
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
