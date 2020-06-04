<div>
    <h2 class="title is-2">Internal Moderation Form</h2>
    <p class="subtitle">For continuous assessments and examinations</p>

    <form action="" method="POST">
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
                            <input class="input" type="text" value="{{ $checklist->course->code }}">
                        </p>
                    </div>
                </div>
                <div class="column">
                    <label for="" class="label">Course Title</label>
                    <p class="control is-expanded">
                        <input class="input" type="text" value="{{ $checklist->course->title }}">
                    </p>
                </div>
            </div>
            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label for="" class="label">Academic Year</label>
                        <p class="control">
                            <input class="input" type="text" value="{{ $checklist->course->year }}">
                        </p>
                    </div>
                </div>
                <div class="column">
                    <label for="" class="label">SCQF Level</label>
                    <p class="control">
                        <input class="input" type="text" value="">
                    </p>

                </div>
                <div class="column">
                    <label for="" class="label">Course Credits</label>
                    <p class="control is-expanded">
                        <input class="input" type="text" value="">
                    </p>

                </div>
            </div>
</div>

<div class="field">
    <label for="" class="label">Course Leader</label>
    <p class="control is-expanded">
        <input class="input" type="text" value="@if (auth()->user()->isSetterFor($checklist->course)) {{ auth()->user()->full_name }} @endif">
    </p>
</div>

<div class="field">
    <label for="" class="label">Please confirm that you have reviewed the Exam Assessment and Continuous Assessment Handbooks for this task.</label>
    <p class="control is-expanded">
        <div class="select is-fullwidth">
            <select>
                <option>Yes</option>
                <option>No</option>
            </select>
        </div>
    </p>
</div>

<div class="field">
    <label for="" class="label">Assessment title and number</label>
    <p class="control is-expanded">
        <input class="input" type="text" value="">
    </p>
</div>
<div class="columns">
    <div class="column">
        <div class="field">
            <label for="" class="label">Assignment weighting</label>
            <p class="control">
                <input class="input" type="text" value="">
            </p>
        </div>

    </div>
    <div class="column">
        <div class="field">
            <label for="" class="label">No. of markers</label>
            <p class="control is-expanded">
                <input class="input" type="text" value="">
            </p>
        </div>


    </div>
</div>

<div class="columns">
    <div class="column">

        <div class="field">
            <label for="" class="label">Name(s) of moderator(s)</label>
            <p class="control">
                <input class="input" type="text" value="">
            </p>
        </div>
    </div>
    <div class="column">
        <div class="field">
            <label for="" class="label">Date assessment passed to moderator</label>
            <p class="control is-expanded">
                <input class="input" type="text" value="">
            </p>
        </div>
    </div>
</div>

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
                <select>
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">If you have answered ‘No’, please indicate why. For example, if you disagreed with the setter’s judgement on any aspect of the assessment task. Please provide evidence and any other details:</label>
        <p class="control is-expanded">
            <textarea class="textarea" name="" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label for="" class="label">Do you recommend that any of the questions should be revised?</label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select>
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">Please indicate the recommended revisions:</label>
        <p class="control is-expanded">
            <textarea class="textarea" name="" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label class="label">Any other comments:</label>
        <p class="control is-expanded">
            <textarea class="textarea" name="" id=""></textarea>
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
                <p class="control is-expanded">
                    <input class="input" type="text" value="">
                </p>
            </div>
        </div>
    </div>

    <div class="field">
        <label class="label">Course Coordinator Comments</label>
        <p class="control is-expanded">
            <textarea class="textarea" name="" id=""></textarea>
        </p>
    </div>

</fieldset>

<fieldset class="mb-8">
    <legend class="notification has-text-weight-semibold" style="width: 100%">
        SECTION C: MODERATOR’S REPORT on the SOLUTIONS
    </legend>

    <div class="field">
        <label for="" class="label">Do you agree that the marks awarded are appropriate?</label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select>
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">If you have answered ‘No’, please indicate why. For example, if you disagreed with the setter’s judgement on any aspect of the marks. Please provide evidence and any other details:</label>
        <p class="control is-expanded">
            <textarea class="textarea" name="" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label for="" class="label">Do you recommend that marks should be adjusted?</label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select>
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">Please indicate the recommended adjustment:</label>
        <p class="control is-expanded">
            <textarea class="textarea" name="" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label class="label">Any further comments</label>
        <p class="control is-expanded">
            <textarea class="textarea" name="" id=""></textarea>
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
                <p class="control is-expanded">
                    <input class="input" type="text" value="">
                </p>
            </div>
        </div>
    </div>

    <div class="field">
        <label class="label">Course Coordinator Comments</label>
        <p class="control is-expanded">
            <textarea class="textarea" name="" id=""></textarea>
        </p>
    </div>

</fieldset>

<fieldset class="mb-8">
    <legend class="notification has-text-weight-semibold" style="width: 100%">
        SECTION D: EXTERNAL EXAMINER’S DECISION: to be completed by the External Examiner (if required)
    </legend>

    <div class="field">
        <label for="" class="label">Name of External Examiner</label>
        <p class="control is-expanded">
            <input type="text" class="input">
        </p>
    </div>

    <div class="field">
        <label for="" class="label">Do you agree to any adjustment suggested by the Moderator?</label>
        <p class="control is-expanded">
            <div class="select is-fullwidth">
                <select>
                    <option>Yes</option>
                    <option>No</option>
                </select>
            </div>
        </p>
    </div>

    <div class="field">
        <label class="label">Please indicate the rationale for your decision:</label>
        <p class="control is-expanded">
            <textarea class="textarea" name="" id=""></textarea>
        </p>
    </div>

    <div class="field">
        <label class="label">Any further comments</label>
        <p class="control is-expanded">
            <textarea class="textarea" name="" id=""></textarea>
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
                <p class="control is-expanded">
                    <input class="input" type="text" value="">
                </p>
            </div>
        </div>
    </div>

</fieldset>
</form>
</div>