
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h3 class="title is-3 has-text-grey">
                {{ ucfirst($checklist->category) }} Paper Checklist for {{ $checklist->course->code }}.
            </h3>
        </div>
    </div>
</div>

<form disabled>
    <div class="columns">
        <div class="column">
            <h4 class="title is-4 has-text-grey">Setter</h4>
            <div class="field">
                <label class="label has-text-grey" for="q1">Question 1</label>
                <div class="control">
                    <textarea class="textarea" id="q1" name="q1">{{ $checklist->q1 }}</textarea>
                </div>
            </div>

            <div class="field">
                <label class="label has-text-grey" for="q2">Question 2</label>
                <div class="control">
                    <textarea class="textarea" id="q2" name="q2">{{ $checklist->q2 }}</textarea>
                </div>
            </div>
        </div>
        <div class="column">
            <h4 class="title is-4 has-text-grey">Moderator</h4>
            <div class="field">
                <label class="label has-text-grey" for="q1">Question 1</label>
                <div class="control">
                    <textarea class="textarea" id="q1" name="blahq1">{{ $checklist->q1 }}</textarea>
                </div>
            </div>

            <div class="field">
                <label class="label has-text-grey" for="q2">Question 2</label>
                <div class="control">
                    <textarea class="textarea" id="q2" name="blahq2">{{ $checklist->q2 }}</textarea>
                </div>
            </div>
        </div>
    </div>
</form>
