@component('mail::message')
# Submission Deadline

Dear exam paper question setter,

This is a reminder to upload your exam paper for the upcoming exam diet and the resit paper by {{ $deadline->format('d/m/Y')}}.
Please remember to complete and upload the checklist as without the checklist, the moderation process will not be triggered.


@component('mail::button', ['url' => '/'])
Log In
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
