@component('mail::message')
# Paper Checklists

The paper checklists you requested are available to download. Please follow the
link below. <em>Note:</em> the link will expire in {{ config('exampapers.zip_expire_hours') }} hours.

@component('mail::button', ['url' => $link])
Paper Checklists
@endcomponent

@include('emails.partials.browser_warning')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
