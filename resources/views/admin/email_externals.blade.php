@extends('layouts.app')

@section('content')

<form method="POST" action="">
    @csrf
    <button class="button">Alert externals about papers</button>
</form>
@endsection
