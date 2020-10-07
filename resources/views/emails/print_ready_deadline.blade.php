@component('mail::message')
# Print-Ready deadline

Dear Teaching Office,

The final version of the following exam papers have not been finalised for printing.

@foreach ($courses as $course)
* {{ $course->code }} {{ $course->title }}
@endforeach

@component('mail::button', ['url' => '/'])
Log In
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
