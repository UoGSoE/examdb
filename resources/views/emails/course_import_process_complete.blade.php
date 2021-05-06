@component('mail::message')
# Import of Course Data is Complete

The import of the course data spreadsheet has now completed.  Exciting!

@component('mail::button', ['url' => route('course.index')])
You can see it here
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
