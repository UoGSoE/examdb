@extends('layouts.app')

@section('content')

<div class="level">
    <div class="level-left">
        <h3 class="title is-3 level-item">
            Exam Paper List
        </h3>
    </div>
    <div class="level-right">
        <a class="button level-item" href="{{ route('admin.paper.export') }}">
            <span class="icon"><i class="fas fa-file-download"></i></span>
            <span>Export Excel</span>
        </a>
        <export-checklists-button></export-checklists-button>
        <export-papers-registry-button></export-papers-registry-button>
        <a class="button level-item" href="{{ route('admin.notify.externals.show') }}">Notify Externals</a>
    </div>
</div>

@livewire('paper-report')

@endsection
