@component('mail::message')
# School of Engineering Exam Papers

The papers you are external for are now ready for your comments. Please
log into the website to view them.

@component('mail::button', ['url' => url('/')])
Exam Paper Database
@endcomponent

Thanks,<br>
School of Engineering Teaching Office
@endcomponent 