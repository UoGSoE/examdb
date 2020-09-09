@extends('layouts.app')

@section('content')

@livewire('paper-checklist', ['course' => $course, 'category' => $category])


@endsection
