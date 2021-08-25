@extends('layouts.app')

@section('content')

<div class="level">
    <div class="level-left">
        <span class="level-item">
            <h3 class="title is-3">
                Course List
            </h3>
        </span>
    </div>
    <div class="level-right">
        <span class="level-item">
            <a href="{{ route('admin.course.export') }}" class="button">
                <span class="icon"><i class="fas fa-file-download "></i></span>
                <span>Export Excel</span>
            </a>
        </span>
        <span class="level-item">
            <a href="{{ route('course.import') }}" class="button">
                <span class="icon"><i class="fas fa-file-upload"></i></span>
                <span>Import Excel</span>
            </a>
        </span>
        <span class="level-item">
            <remove-staff-button></remove-staff-button>
        </span>
    </div>
</div>

@livewire('course-index')

@endsection
