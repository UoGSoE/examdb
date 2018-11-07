@component('mail::message')
# External Comments Added

A an external has added comments to a course you are working on.  Please click the link
below to see it.

@component('mail::button', ['url' => route('course.show', $paper->course)])
Papers for {{ $paper->course->code }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
