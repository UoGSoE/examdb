@extends('layouts.app')

@section('content')

<a href="/dashboard/users"> >> Edit Sysadmins</a>
<hr>

@livewire('tenant-editor')

@endsection
