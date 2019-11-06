@component('mail::message')
# Papers for Registry

The exam papers for registry are ready to be downloaded.  Please click the link below to get the ZIP file.
<em>Note:</em> the link will expire in {{ config('exampapers.zip_expire_hours') }} hours.

</template>

@component('mail::button', ['url' => $link])
Download the Papers
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
