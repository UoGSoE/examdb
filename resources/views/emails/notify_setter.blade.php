@component('mail::message')
# Exam Paper Added

A paper has been added to a course you are setter for.  Please click the link
below to see it.

@component('mail::button', ['url' => route('course.show', $paper->course)])
Papers for {{ $paper->course->code }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
