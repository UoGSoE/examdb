@component('mail::message')
# ExamDB - Paper for registry uploaded

The paper for registry has been uploaded for {{ $course->code }}.

@component('mail::button', ['url' => route('course.show', $course->id)])
View {{ $course->code }}
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
