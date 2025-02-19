<div>
    <div class="field">
        <div class="control">
            <input class="input is-small @error('courseData.semester') has-text-danger @enderror" type="number" wire:model.live="courseData.semester" min="1" max="3">
        </div>
    </div>
</div>
