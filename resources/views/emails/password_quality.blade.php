@component('mail::message')
# Password and haveibeenp0wned check for examdb

Failed check for {{ $username }}.  Errors are :

@foreach ($errors as $error)
* {{ $error }}
@endforeach

@endcomponent
