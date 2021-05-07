@component('mail::message')
# Import of Course Data is Complete

The import of the course data spreadsheet has now completed.  Exciting!

@component('mail::button', ['url' => route('course.index')])
You can see it here
@endcomponent

@if (count($errors) > 0)
The following errors occurred while importing :
@foreach ($errors as $error)
* {{ $error }}
@endforeach
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
