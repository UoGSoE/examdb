@component('mail::message')
# Exam paper moderation

Dear exam paper moderator,

There is an exam paper ready for you to moderate for {{ $course->code }}.  @if ($deadline) Moderation deadline is {{ $deadline }}. @endif
Please remember to complete and upload the checklist as without the checklist, the moderation process will not be triggered.

@component('mail::button', ['url' => route('course.show', $course->id)])
Log in
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
