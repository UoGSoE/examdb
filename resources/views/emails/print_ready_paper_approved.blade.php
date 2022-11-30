<x-mail::message>
# The Print Ready Paper for {{ $course->code }} has been approved

<x-mail::button url="{{ route('course.show', $course) }}">
View the course
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
