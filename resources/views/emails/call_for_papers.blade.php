@component('mail::message')
# Exams: Call for papers

Dear exam paper question setter,  Please upload your exam paper for the upcoming exam diet and the resit paper by {{ $deadlineGlasgow->format('d/m/Y') }} for Glasgow and {{ $deadlineUestc->format('d/m/Y') }} for UESTC.
Please remember to complete and upload the checklist as without the checklist, the moderation process will not be triggered.

@component('mail::button', ['url' => url('/')])
Log in here
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
