<div>
    @if ($course->hasPreviousChecklists($checklist['category']))
        <div class="field">
            <label for="" class="label">Previous Versions</label>
            <p class="control is-expanded">
                <div class="select is-fullwidth">
                    <select name="previous_id">
                        @foreach ($course->checklists as $checklist)
                            <option value="{{ $checklist->id }}">{{ $checklist->created_at->format('d/m/Y H:i') }}</option>
                        @endforeach
                    </select>
                </div>
            </p>
        </div>
    @endif
    <h2 class="title is-2">Internal Moderation Form for <span class="has-text-weight-bold">{{ ucfirst($checklist['category']) }}</span> Paper</h2>
    <p class=" subtitle">For continuous assessments and examinations</p>

    <form action="" method="">
        @csrf

        <fieldset class="mb-8">
            <legend class="notification has-text-weight-semibold" style="width: 100%">
                SECTION A: COURSE AND ASSESSMENT DETAILS: to be completed by the Course Coordinator
            </legend>
            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label for="" class="label">Course Code</label>
                        <p class="control">
                            <input class="input" type="text" wire:model="checklist.fields.course_code">
                        </p>
                    </div>
                </div>
                <div class="column">
                    <label for="" class="label">Course Title</label>
                    <p class="control is-expanded">
                        <input class="input" type="text" wire:model.lazy="checklist.fields.course_title">
                    </p>
                </div>
            </div>
            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label for="" class="label">Academic Year</label>
                        <p class="control">
                            <input class="input" type="text" wire:model.lazy="checklist.fields.year">
                        </p>
                    </div>
                </div>
                <div class="column">
                    <label for="" class="label">SCQF Level</label>
                    <p class="control">
                        <input class="input" type="text" wire:model.lazy="checklist.fields.scqf_level">
                    </p>
                </div>
                <div class="column">
                    <label for="" class="label">Course Credits</label>
                    <p class="control is-expanded">
                        <input class="input" type="text" wire:model.lazy="checklist.course.credits">
                    </p>
                </div>
            </div>


<div class="field">
    <label for="" class="label">Please confirm that you have reviewed the Exam Assessment and Continuous Assessment Handbooks for this task.</label>
    <p class="control is-expanded">
        <div class="select is-fullwidth">
            <select wire:model="checklist.fields.setter_reviews">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
    </p>
</div>

<div class="field">
    <label for="" class="label">Assessment title and number</label>
    <p class="control is-expanded">
        <input class="input" type="text" wire:model="checklist.fields.assessment_title">
    </p>
</div>
<div class="columns">
    <div class="column">
        <div class="field">
            <label for="" class="label">Assignment weighting</label>
            <p class="control">
                <input class="input" type="text" wire:model="checklist.fields.assignment_weighting">
            </p>
        </div>

    </div>
    <div class="column">
        <div class="field">
            <label for="" class="label">No. of markers</label>
            <p class="control is-expanded">
                <input class="input" type="text" wire:model="checklist.fields.number_markers">
            </p>
        </div>


    </div>
</div>

<div class="columns">
    <div class="column">

        <div class="field">
            <label for="" class="label">Name(s) of moderator(s)</label>
            <p class="control">
                <input class="input" type="text" value="{{ $course->moderators->pluck('full_name')->implode(', ') }}">
            </p>
        </div>
    </div>
    <div class="column">
        <div class="field">
            <label for="" class="label">Date assessment passed to moderator</label>
            <p
                class="control is-expanded"
                x-data="{}"
                x-init="new Pikaday({ field: $refs.passed_to_moderator, format: 'DD/MM/YYYY' })"
            >
                <input class="input" x-ref="passed_to_moderator" type="text" wire:model.lazy="checklist.fields.passed_to_moderator">
            </p>
        </div>
    </div>
</div>
<div class="field">
    <div class="control">
        <button wire:click.prevent="save" class="button">Save</button>
    </div>
</div>
<hr>
</fieldset>

<fieldset class="mb-8">
    <legend class="notification has-text-weight-semibold" style="width: 100%">
        SECTION B: MODERATOR’S REPORT on the ASSESSMENT task<br />
        1. Comment on the overall exam layout, difficulty of the questions and the range of marks for each question.<br />
        2. Any issues or suggestions for the marking team.
    </legend>

    <div class="field">
        <label for="" class="label">Is the overall quality of the assessment task appropriate?</label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select wire:model="checklist.overall_quality_appropriate">
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">If you have answered ‘No’, please indicate why. For example, if you disagreed with the setter’s judgement on any aspect of the assessment task. Please provide evidence and any other details:</label>
        <p class="control is-expanded">
            <textarea class="textarea" wire:model="checklist.why_innapropriate" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label for="" class="label">Do you recommend that any of the questions should be revised?</label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select wire:model="checklist.should_revise_questions">
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">Please indicate the recommended revisions:</label>
        <p class="control is-expanded">
            <textarea class="textarea" wire:model="checklist.recommended_revisions" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label class="label">Any other comments:</label>
        <p class="control is-expanded">
            <textarea class="textarea" wire:model="checklist.moderator_comments" id=""></textarea>
        </p>
    </div>

    <div class="columns">
        <div class="column">

            <div class="field">
                <label for="" class="label">Moderators e-signature</label>
                <p class="control">
                    <input class="input" type="text" value="">
                </p>
            </div>
        </div>
        <div class="column">
            <div class="field">
                <label for="" class="label">Date completed</label>
                <p
                    class="control is-expanded"
                    x-data="{}"
                    x-init="new Pikaday({ field: $refs.moderator_completed_at, format: 'DD/MM/YYYY' })"
                >
                    <input class="input" x-ref="moderator_completed_at" type="text" wire:model.lazy="checklist.moderator_completed_at">
                </p>
            </div>
        </div>
    </div>

    <div class="field">
        <label class="label">Course Coordinator Comments</label>
        <p class="control is-expanded">
            <textarea class="textarea" wire:model="checklist.setter_comments_to_moderator" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <div class="control">
            <button class="button">Save</button>
        </div>
    </div>

    <hr>

</fieldset>

<fieldset class="mb-8">
    <legend class="notification has-text-weight-semibold" style="width: 100%">
        SECTION C: MODERATOR’S REPORT on the SOLUTIONS
    </legend>

    <div class="field">
        <label for="" class="label">Do you agree that the marks awarded are appropriate?</label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select wire:model="checklist.solution_marks_appropriate">
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">If you have answered ‘No’, please indicate why. For example, if you disagreed with the setter’s judgement on any aspect of the marks. Please provide evidence and any other details:</label>
        <p class="control is-expanded">
            <textarea class="textarea" wire:model="checklist.moderator_solution_innapropriate_comments" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label for="" class="label">Do you recommend that marks should be adjusted?</label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select wire:model="checklist.solutions_marks_adjusted">
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">Please indicate the recommended adjustment:</label>
        <p class="control is-expanded">
            <textarea class="textarea" wire:model="checklist.solution_adjustment_comments" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label class="label">Any further comments</label>
        <p class="control is-expanded">
            <textarea class="textarea" wire:model="checklist.solution_moderator_comments" id=""></textarea>
        </p>
    </div>

    <div class="columns">
        <div class="column">
            <div class="field">
                <label for="" class="label">Moderators e-signature</label>
                <p class="control">
                    <input class="input" type="text" value="">
                </p>
            </div>
        </div>
        <div class="column">
            <div class="field">
                <label for="" class="label">Date completed</label>
                <p
                    class="control is-expanded"
                    x-data="{}"
                    x-init="new Pikaday({ field: $refs.moderator_solutions_at, format: 'DD/MM/YYYY' })"
                >
                    <input class="input" x-ref="moderator_solutions_at" type="text" wire:model.lazy="checklist.moderator_solutions_at">
                </p>
            </div>
        </div>
    </div>

    <div class="field">
        <label class="label">Course Coordinator Comments</label>
        <p class="control is-expanded">
            <textarea class="textarea" wire:model="checklist.solution_setter_comments" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <div class="control">
            <button class="button">Save</button>
        </div>
    </div>

    <hr>

</fieldset>

<fieldset class="mb-8">
    <legend class="notification has-text-weight-semibold" style="width: 100%">
        SECTION D: EXTERNAL EXAMINER’S DECISION: to be completed by the External Examiner (if required)
    </legend>

    <div class="field">
        <label for="" class="label">Name of External Examiner</label>
        <p class="control is-expanded">
            <input type="text" class="input" wire:model="checklist.external_examiner_name">
        </p>
    </div>

    <div class="field">
        <label for="" class="label">Do you agree to any adjustment suggested by the Moderator?</label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select wire:model="checklist.external_agrees_with_moderator">
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">Please indicate the rationale for your decision:</label>
        <p class="control is-expanded">
            <textarea class="textarea" wire:model="checklist.external_reason" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label class="label">Any further comments</label>
        <p class="control is-expanded">
            <textarea class="textarea" wire:model="checklist.external_comments" id=""></textarea>
        </p>
    </div>

    <div class="columns">
        <div class="column">

            <div class="field">
                <label for="" class="label">e-Signature</label>
                <p class="control">
                    <input class="input" type="text" value="">
                </p>
            </div>
        </div>
        <div class="column">
            <div class="field">
                <label for="" class="label">Date completed</label>
                <p
                    class="control is-expanded"
                    x-data="{}"
                    x-init="new Pikaday({ field: $refs.external_signed_at, format: 'DD/MM/YYYY' })"
                >
                    <input class="input" x-ref="external_signed_at" type="text" wire:model="checklist.external_signed_at">
                </p>
            </div>
        </div>
    </div>
    <div class="field">
        <div class="control">
            <button class="button">Save</button>
        </div>
    </div>
</fieldset>
</form>
</div>
