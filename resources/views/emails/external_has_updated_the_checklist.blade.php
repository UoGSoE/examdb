@component('mail::message')
# Exam paper moderation

Dear exam paper setter,

The External Examiner has reviewed your exam paper for {{ $course->code }} and has uploaded comments.
Please check the comments and make the adjustments to your papers as required.
Please remember to complete and upload the checklist as without the checklist, the moderation process will not be triggered.

@component('mail::button', ['url' => route('course.show', $course->id)])
Log in
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
