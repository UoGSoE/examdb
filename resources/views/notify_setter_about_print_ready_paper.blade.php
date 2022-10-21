@component('mail::message')
# Print Ready Paper

This is to let you know that the Teaching Office have uploaded the final print-ready version of the paper for the course {{ $course->code }}.
Please double-check it if you have time.

@component('mail::button', ['url' => route('course.show', $course)])
View {{ $course->code }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
