@component('mail::message')
# Paper Checklist Updated for {{ $checklist->course->code }}

The {{ ucfirst($checklist->category) }} Paper Checklist has been updated for {{ $checklist->course->code }}.  This means there are some documents
for you to look at.

@component('mail::button', ['url' => route('course.show', $checklist->course->id)])
Log in to the Exam Database
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
