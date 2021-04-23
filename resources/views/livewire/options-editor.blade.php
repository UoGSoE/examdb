  <div>
      <h2 class="title is-2">Options</h2>

      <form method="POST">
          <div class="field">
              <label class="label">General Teaching Office Email</label>
              <div class="control">
                  <input class="input" type="email" wire:model.lazy="options.teaching_office_contact">
              </div>
          </div>
          @error('options.teaching_office_contact')
          <p class="has-text-danger">{{ $message }}</p>
          @enderror

          @foreach ($defaultDateOptions as $option)
          <label class="label">{{ $option['label'] }}</label>
          <div class="field has-addons">
              <div
                class="control is-expanded"
                x-data="{}"
                x-init="new Pikaday({ field: $refs.{{$option['name']}}, format: 'DD/MM/YYYY' })"
              >
                  <input class="input" type="text" x-ref="{{ $option['name'] }}" wire:model.lazy="options.{{ $option['name'] }}">
              </div>
          </div>
          @error('options.' . $option['name'])
          <p class="has-text-danger">{{ $message }}</p>
          @enderror
          @endforeach

          <div class="field">
              <div class="control">
                  <button class="button" wire:click.prevent="save" @if ($wasSaved) disabled @endif>@if ($wasSaved) Saved @else Save @endif</button>
              </div>
          </div>
      </form>
  </div>
