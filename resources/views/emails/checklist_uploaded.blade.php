@component('mail::message')
# Paper Checklist Uploaded for {{ $paper->course->code }}

A Paper Checklist has been uploaded for {{ $paper->course->code }}.  This means there are some documents
for you to look at.

@component('mail::button', ['url' => route('course.show', $paper->course->id)])
Log in to the Exam Database
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
