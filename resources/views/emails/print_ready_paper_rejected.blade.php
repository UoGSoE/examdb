<x-mail::message>
# The Print Ready Paper for {{ $course->code }} has been rejected

The reason given was:

> {{ $reason }}

<x-mail::button url="{{ route('course.show', $course) }}">
Log in
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
