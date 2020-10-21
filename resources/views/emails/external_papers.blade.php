@component('mail::message')
# School of Engineering Exam Papers

The papers you are the external for are now ready for your comments. Please
log into the website to view them.

@component('mail::button', ['url' => url('/')])
Exam Paper Database
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
School of Engineering Teaching Office
@endcomponent
