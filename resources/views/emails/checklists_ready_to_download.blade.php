@component('mail::message')
# Paper Checklists

The paper checklists you requests are available to download. Please follow the
link below. <em>Note:</em> the link will expire in {{ config('exampapers.zip_expire_hours') }} hours.

@component('mail::button', ['url' => $link])
Paper Checklists
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent