@component('mail::message')
# Moderation deadline has passed.

Dear exam paper moderator,  This is a reminder to moderate the exam papers for the upcoming exam
diet and the resit paper by {{ $deadline->format('d/m/Y') }}. Please remember to complete and upload the
checklist as without the checklist, the moderation process will not be triggered.

@component('mail::button', ['url' => '/'])
Log in
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
