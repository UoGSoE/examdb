@component('mail::message')
# Sync from the Management DB complete

This is just to let you know that the sync from the Management DB to the Exam DB completed.

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
