<div>
    @if ($course->hasPreviousChecklists($checklist['category']))
        <label for="previous_id" class="label">Previous Versions</label>
        <div class="field has-addons">
            <p class="control is-expanded">
                <div class="select is-fullwidth">
                    <select name="previous_id" id="previous_id" wire:model="previousId">
                        @foreach ($course->checklists as $previousChecklist)
                            <option value="{{ $previousChecklist->id }}">{{ $previousChecklist->created_at->format('d/m/Y H:i') }}</option>
                        @endforeach
                        <option value="new">New Checklist</option>
                    </select>
                </div>
            </p>
            <div class="control">
                <button class="button" wire:click="showExistingChecklist">View</button>
            </div>
        </div>
    @endif
    <h2 class="title is-2">Internal Moderation Form for <span class="has-text-weight-bold">{{ ucfirst($checklist['category']) }}</span> Paper</h2>
    <p class=" subtitle">For continuous assessments and examinations</p>

    <form action="" method="">
        @csrf

        <fieldset class="mb-8" @if (! auth()->user()->isSetterFor($course)) disabled @endif>
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
                        <input class="input" type="text" wire:model.lazy="checklist.fields.course_credits">
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
            <label for="" class="label">Assessment weighting</label>
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
                <input class="input" type="text" wire:model="checklist.fields.moderators">
            </p>
        </div>
    </div>
    <div class="column">
        <div class="field">
            <label for="" class="label">Date assessment passed to moderator </label>
            <p
                class="control is-expanded"
                x-data="{}"
                x-init="new Pikaday({ field: $refs.passed_to_moderator, format: 'DD/MM/YYYY' })"
            >
                <input class="input" x-ref="passed_to_moderator" type="text" wire:model.lazy="checklist.fields.passed_to_moderator">
            </p>
        <span class="help">(Note: setting/changing this will email the @choice('moderator|moderators', $course->moderators))</span>
        </div>
    </div>
</div>
@if (auth()->user()->isSetterFor($course) && !isset($checklist['id']))
<div class="field">
    <div class="control">
        <button wire:click.prevent="save" class="button">Save</button>
    </div>
</div>
@endif
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
                <select wire:model="checklist.fields.overall_quality_appropriate" @if (! auth()->user()->isModeratorFor($course)) disabled @endif>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">If you have answered ‘No’, please indicate why. For example, if you disagreed with the setter’s judgement on any aspect of the assessment task. Please provide evidence and any other details:</label>
        <p class="control is-expanded">
            <textarea class="textarea"  @if (! auth()->user()->isModeratorFor($course)) disabled @endif wire:model="checklist.fields.why_innapropriate" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label for="" class="label">
            Do you recommend that any of the questions should be revised?  By ANSWERING YES the paper will
            be returned to the setter to make adjustments.  By ANSWERING NO you are happy with the paper to move to the next stage.
        </label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select wire:model="checklist.fields.should_revise_questions" @if (! auth()->user()->isModeratorFor($course)) disabled @endif>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">Please indicate the recommended revisions:</label>
        <p class="control is-expanded">
            <textarea class="textarea" @if (! auth()->user()->isModeratorFor($course)) disabled @endif wire:model="checklist.fields.recommended_revisions" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label class="label">Any other comments:</label>
        <p class="control is-expanded">
            <textarea class="textarea" @if (! auth()->user()->isModeratorFor($course)) disabled @endif wire:model="checklist.fields.moderator_comments" id=""></textarea>
        </p>
    </div>

    <div class="columns">
        <div class="column">

            <div class="field">
                <label for="" class="label">Moderators e-signature</label>
                <p class="control">
                    <input class="input" type="text" wire:model="checklist.fields.moderator_esignature" @if (! auth()->user()->isModeratorFor($course)) disabled @endif>
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
                    <input class="input" @if (! auth()->user()->isModeratorFor($course)) disabled @endif x-ref="moderator_completed_at" type="text" wire:model.lazy="checklist.fields.moderator_completed_at">
                </p>
            </div>
        </div>
    </div>

    <div class="field">
        <label class="label">Course Coordinator Comments</label>
        <p class="control is-expanded">
            <textarea class="textarea" @if (! auth()->user()->isSetterFor($course) or ($course->isApprovedByModerator($checklist['category']))) disabled @endif wire:model="checklist.fields.setter_comments_to_moderator" id=""></textarea>
        </p>
    </div>

    {{--
    @if ((auth()->user()->isModeratorFor($course) or auth()->user()->isSetterFor($course)) && !isset($checklist['id']))
    <div class="field">
        <div class="control">
            <button class="button" wire:click.prevent="save">Save</button>
        </div>
    </div>
    @endif
    --}}
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
                <select wire:model="checklist.fields.solution_marks_appropriate" @if (! auth()->user()->isModeratorFor($course)) disabled @endif>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">If you have answered ‘No’, please indicate why. For example, if you disagreed with the setter’s judgement on any aspect of the marks. Please provide evidence and any other details:</label>
        <p class="control is-expanded">
            <textarea class="textarea" @if (! auth()->user()->isModeratorFor($course)) disabled @endif wire:model="checklist.fields.moderator_solution_innapropriate_comments" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label for="" class="label">Do you recommend that marks should be adjusted?</label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select @if (! auth()->user()->isModeratorFor($course)) disabled @endif wire:model="checklist.fields.solutions_marks_adjusted">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">Please indicate the recommended adjustment:</label>
        <p class="control is-expanded">
            <textarea @if (! auth()->user()->isModeratorFor($course)) disabled @endif class="textarea" wire:model="checklist.fields.solution_adjustment_comments" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label class="label">Any further comments</label>
        <p class="control is-expanded">
            <textarea @if (! auth()->user()->isModeratorFor($course)) disabled @endif class="textarea" wire:model="checklist.fields.solution_moderator_comments" id=""></textarea>
        </p>
    </div>

    <div class="columns">
        <div class="column">
            <div class="field">
                <label for="" class="label">Moderators e-signature</label>
                <p class="control">
                    <input class="input" type="text" wire:model="checklist.fields.moderator_esignature" @if (! auth()->user()->isModeratorFor($course)) disabled @endif>
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
                    <input class="input" @if (! auth()->user()->isModeratorFor($course)) disabled @endif x-ref="moderator_solutions_at" type="text" wire:model.lazy="checklist.fields.moderator_solutions_at">
                </p>
            </div>
        </div>
    </div>

    <div class="field">
        <label class="label">Course Coordinator Comments</label>
        <p class="control is-expanded">
            <textarea class="textarea" @if (! auth()->user()->isSetterFor($course) or ($course->isApprovedByModerator($checklist['category']))) disabled @endif wire:model="checklist.fields.solution_setter_comments" id=""></textarea>
        </p>
    </div>

    @if ((auth()->user()->isModeratorFor($course) or auth()->user()->isSetterFor($course)) && !isset($checklist['id']))
    <div class="field">
        <div class="control">
            <button  wire:click.prevent="save" class="button">Save</button>
        </div>
    </div>
    @endif

    <hr>

</fieldset>

<fieldset class="mb-8">
    <legend class="notification has-text-weight-semibold" style="width: 100%">
        SECTION D: EXTERNAL EXAMINER’S DECISION: to be completed by the External Examiner (if required)
    </legend>

    <div class="field">
        <label for="" class="label">Name of External Examiner</label>
        <p class="control is-expanded">
            <input type="text" @if (! auth()->user()->isExternalFor($course)) disabled @endif class="input" wire:model="checklist.fields.external_examiner_name">
        </p>
    </div>

    <div class="field">
        <label for="" class="label">Are you satisfied with this paper?</label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select @if (! auth()->user()->isExternalFor($course)) disabled @endif wire:model="checklist.fields.external_agrees_with_moderator">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">Please indicate the rationale for your decision:</label>
        <p class="control is-expanded">
            <textarea @if (! auth()->user()->isExternalFor($course)) disabled @endif class="textarea" wire:model="checklist.fields.external_reason" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label class="label">Any further comments</label>
        <p class="control is-expanded">
            <textarea @if (! auth()->user()->isExternalFor($course)) disabled @endif class="textarea" wire:model="checklist.fields.external_comments" id=""></textarea>
        </p>
    </div>

    <div class="columns">
        <div class="column">

            <div class="field">
                <label for="" class="label">e-Signature</label>
                <p class="control">
                    <input class="input" type="text" value="" @if (! auth()->user()->isExternalFor($course)) disabled @endif>
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
                    <input class="input" @if (! auth()->user()->isExternalFor($course)) disabled @endif x-ref="external_signed_at" type="text" wire:model="checklist.fields.external_signed_at">
                </p>
            </div>
        </div>
    </div>

    @if (auth()->user()->isExternalFor($course) && !isset($checklist['id']))
    <div class="field">
        <div class="control">
            <button wire:click.prevent="save" class="button">Save</button>
        </div>
    </div>
    @endif
</fieldset>
</form>
</div>
