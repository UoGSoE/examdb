@component('mail::message')
# Login to UoG School of Engineering Exam Papers

Please follow the link below to log into the University of Glasgow School of Engineering Exam Papers system.  The
link is valid for {{ config('exampapers.login_link_minutes', 60) }} minutes from now.

@component('mail::button', ['url' => $user->generateLoginUrl()])
Login to the Exam Papers System
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
