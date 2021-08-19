<div>
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <div class="field is-grouped">
                    <p class="control">
                        <button class="button" wire:click.prevent="$set('disciplineFilter', null)">
                            All
                        </button>
                    </p>
                    @foreach ($disciplines as $discipline)
                    <p class="control">
                        <button wire:click.prevent="$set('disciplineFilter', {{ $discipline->id }})" class="button @if ($discipline->id == $disciplineFilter) is-info @endif" @if ($discipline->id == $disciplineFilter) disabled @endif
                            >
                            {{ $discipline->title }}
                        </button>
                    </p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <div class="field">
                    <p class="control">
                        <input wire:model="searchTerm" type="text" class="input" placeholder="Search course code or title..." autofocus>
                    </p>
                </div>
            </div>
            <div class="level-item">
                <div class="field is-grouped">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" wire:model="includeTrashed">
                            Include disabled?
                        </label>
                    </div>
                    <div class="control">
                        <label class="checkbox">
                        <input type="checkbox" wire:model="excludeNotExamined">
                        Exclude non-examined?
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <table class="table is-striped is-fullwidth is-narrow is-hoverable">
        <thead>
            <tr>
                <th width="10%">Code</th>
                <th width="2%">Semester</th>
                <th>Title</th>
                <th width="7%">Discipline</th>
                <th>Main</th>
                <th>Resit</th>
                <th>Setters</th>
                <th>Moderators</th>
                <th>Externals</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($courseList as $course)
            <tr wire:key="course-{{ $course->id }}">
                <td width="5%" @if ($course->isntExamined()) class="has-background-warning" title="Not examined" @endif>
                    <a @if (!$course->trashed()) href="{{ route('course.show', $course) }}" @endif>
                        <span>
                        {{ $course->code }}
                        @if ($course->trashed())
                        <span class="tag is-warning">Disabled</span>
                        @endif
                        </span>
                    </a>
                </td>
                <td>
                    @livewire('semester-edit-box', ['course' => $course], key($course->id))
                </td>
                <td>{{ $course->title }}</td>
                <td>{{ optional($course->discipline)->title }}</td>
                <td>
                    <span class="icon {{ $course->hasSetterChecklist('main') ? 'has-text-info' : 'has-text-grey-light' }}" title="Setter has filled checklist?">
                        <i class="fas fa-user-tie"></i>
                    </span>
                    <span class="icon {{ $course->isApprovedByModerator('main') ? 'has-text-info' : 'has-text-grey-light' }}" title="Moderator approved?">
                        <i class="fas fa-user-graduate"></i>
                    </span>
                    <span class="icon {{ $course->hasExternalChecklist('main') ? 'has-text-info' : 'has-text-grey-light' }}" title="External has filled checklist?">
                        <i class="fas fa-user-secret"></i>
                    </span>
                </td>
                <td>
                    <span class="icon {{ $course->hasSetterChecklist('resit') ? 'has-text-info' : 'has-text-grey-light' }}" title="Setter has filled checklist?">
                        <i class="fas fa-user-tie"></i>
                    </span>
                    <span class="icon {{ $course->isApprovedByModerator('resit') ? 'has-text-info' : 'has-text-grey-light' }}" title="Moderator approved?">
                        <i class="fas fa-user-graduate"></i>
                    </span>
                    <span class="icon {{ $course->hasExternalChecklist('resit') ? 'has-text-info' : 'has-text-grey-light' }}" title="External has filled checklist?">
                        <i class="fas fa-user-secret"></i>
                    </span>
                </td>
                <td>
                    {!! $course->setters->userLinks()->implode(', ') !!}
                </td>
                <td>
                    {!! $course->moderators->userLinks()->implode(', ') !!}
                </td>
                <td>
                    {!! $course->externals->userLinks()->implode(', ') !!}
                </td>
                <td>
                    @if ($course->trashed())
                    <form action="{{ route('course.enable', $course->id) }}" method="POST">
                        @csrf
                        <button class="button is-small">Re-enable</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
