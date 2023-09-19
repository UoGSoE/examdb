<x-mail::message>
# Print Ready Paper Reminder

You have Print Ready Papers to review for the following courses :

@foreach($courseCodes as $code)
* {{ $code }}
@endforeach

<x-mail::button url="{{ route('home') }}">
Visit the ExamDB
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
