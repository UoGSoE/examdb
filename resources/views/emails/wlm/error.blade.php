@component('mail::message')
# ExamDB

There was a problem while importing the WLM data into the ExamDB.  Exception message was :

{{ $exceptionMessage }}

@include('emails.partials.browser_warning')

Have a simply lovely day!
@endcomponent
