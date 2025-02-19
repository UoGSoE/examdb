@extends('components.layouts.app')

@section('content')

<notify-externals-form :disciplines="{{ $disciplines->toJson() }}"></notify-externals-form>

@endsection
