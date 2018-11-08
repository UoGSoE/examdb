@extends('layouts.app')

@section('content')

<h3 class="title is-3">
    Course List
</h3>

<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Course</th>
            <th>Main</th>
            <th>Resit</th>
            <th>Setters</th>
            <th>Moderators</th>
            <th>Externals</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($courses as $course)
            <tr>
                <td>
                    {{ $course->code }}
                </td>
                <td>
                    <div class="field has-addons">
                        <p class="control">
                            <span class="icon {{ $course->isApprovedBySetter('main') ? 'has-text-info' : 'has-text-grey-light' }}">
                                <i class="fas fa-user"></i>
                            </span>
                        </p>
                        <p class="control">
                            <span class="icon {{ $course->isApprovedByModerator('main') ? 'has-text-info' : 'has-text-grey-light' }}">
                                <i class="fas fa-user-graduate"></i>
                            </span>
                        </p>
                        <p class="control">
                            <span class="icon has-text-grey-light">
                                <i class="fas fa-user-lock"></i>
                            </span>
                        </p>
                    </div>
                </td>
                <td>
                    <div class="field has-addons">
                        <p class="control">
                            <span class="icon {{ $course->isApprovedBySetter('resit') ? 'has-text-success' : 'has-text-grey-light' }}">
                                <i class="fas fa-user"></i>
                            </span>
                        </p>
                        <p class="control">
                            <span class="icon {{ $course->isApprovedByModerator('resit') ? 'has-text-success' : 'has-text-grey-light' }}">
                                <i class="fas fa-user-graduate"></i>
                            </span>
                        </p>
                        <p class="control">
                            <span class="icon has-text-grey-light">
                                <i class="fas fa-user-lock"></i>
                            </span>
                        </p>
                    </div>
                </td>
                <td>
                    {{ $course->setters->pluck('full_name')->implode(', ') }}
                </td>
                <td>
                    {{ $course->moderators->pluck('full_name')->implode(', ') }}
                </td>
                <td>
                    {{ $course->externals->pluck('full_name')->implode(', ') }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection