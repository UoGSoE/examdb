<div>
    <div class="field is-grouped">
        <p class="control"><button class="button is-success" disabled>Discipline</button></p>
        <p class="control">
            <button class="button @if (! $disciplineFilter) is-info @endif" @if (! $disciplineFilter) disabled @endif wire:click="$set('disciplineFilter', null)">
                All
            </button>
        </p>
        @foreach ($disciplines as $discipline)
            <p class="control">
                <button wire:click="$set('disciplineFilter', {{ $discipline->id }})" class="button @if ($discipline->id == $disciplineFilter) is-info @endif" @if ($discipline->id == $disciplineFilter) disabled @endif
                    >
                    {{ $discipline->title }}
                </button>
            </p>
        @endforeach
        <p class="control"><button class="button is-success" disabled>Semester</button></p>
        <div class="control">
            <div class="select">
              <select wire:model="semesterFilter">
                <option value="">All</option>
                <option value="1">1</option>
                <option value="2">2</option>
              </select>
            </div>
        </div>
        <p class="control"><button class="button is-success" disabled>Category</button></p>
        <div class="control">
            <div class="select">
              <select wire:model="categoryFilter">
                <option value="">All</option>
                <option value="main">Main</option>
                <option value="resit">Resit</option>
              </select>
            </div>
        </div>
    </div>

    <table class="table is-fullwidth is-striped is-hoverable is-bordered">
        <thead>
            <tr>
                <th width="15%">Code</th>
                <th>Semester</th>
                <th>Title</th>
                <th>Discipline</th>
                <th>Check Lists</th>
                <th>Pre Internally moderated</th>
                <th>Moderator Comments</th>
                <th>Post Internally moderated</th>
                <th>External Comments</th>
                <th>Final Paper for Registry</th>
                <th>Print Ready Paper</th>
                <th>Print Ready Approved</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courses as $course)
                @foreach(['main', 'resit'] as $category)
                    @if ($categoryFilter && $categoryFilter != $category)
                        @continue
                    @endif
                    <tr wire:key="course-{{ $course->id }}-{{ $course->code }}-{{ $category }}">
                        <td>
                            {{ $course->code }} <span class="tag">{{ $category }}</span>
                        </td>
                        <td>{{ $course->semester }}</td>
                        <td>{{ $course->title }}</td>
                        <td>
                            {{ $course->discipline?->title }}
                        </td>
                        <td>
                            <span class="icon {{ $course->hasSetterChecklist($category) ? 'has-text-info' : 'has-text-grey-light' }}" title="Setter Checklist">
                                <i class="fas fa-user-tie"></i>
                            </span>
                            <span class="icon {{ $course->hasModeratorChecklist($category) ? 'has-text-info' : 'has-text-grey-light' }}" title="Moderator Checklist">
                                <i class="fas fa-user-graduate"></i>
                            </span>
                        </td>
                        <td>
                            {{ $course->datePaperAdded($category, \App\Models\Paper::PRE_INTERNALLY_MODERATED) }}
                        </td>
                        <td>
                            {{ $course->dateModeratorFilledChecklist($category) }}
                        </td>
                        <td>
                            {{ $course->datePaperAdded($category, \App\Models\Paper::MODERATOR_COMMENTS) }}
                        </td>
                        <td>
                            {{ $course->dateExternalFilledChecklist($category) }}
                        </td>
                        <td>
                            {{ $course->datePaperAdded($category, \App\Models\Paper::PAPER_FOR_REGISTRY) }}
                        </td>
                        <td id="print-ready-date">
                            {{ $course->datePaperAdded($category, \App\Models\Paper::ADMIN_PRINT_READY_VERSION) }}
                        </td>
                        <td id="print-ready-status">
                            @if ($course->printReadyPaperRejected($category))
                                <span class="tag is-danger" title="Rejected">
                                    Rejected
                                </span>
                                {{ $course->printReadyPaperRejectedMessage($category) }}
                            @else
                                {{ $course->printReadyPaperApproved($category) ? 'Yes' : 'No' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
