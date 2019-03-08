@extends('layouts.app')

@section('content')

<options-editor :options='@json($options)' />

@endsection 