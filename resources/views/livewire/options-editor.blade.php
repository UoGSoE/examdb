  <div>
      <h2 class="title is-2">Options</h2>

      <form method="POST">
          <div class="field">
              <label class="label">Glasgow General Teaching Office Email</label>
              <div class="control">
                  <input class="input" type="email" wire:model.lazy="options.teaching_office_contact_glasgow">
              </div>
          </div>
          @error('options.teaching_office_contact_glasgow')
          <p class="has-text-danger">{{ $message }}</p>
          @enderror

          <div class="field">
              <label class="label">UESTC General Teaching Office Email</label>
              <div class="control">
                  <input class="input" type="email" wire:model.lazy="options.teaching_office_contact_uestc">
              </div>
          </div>
          @error('options.teaching_office_contact_uestc')
          <p class="has-text-danger">{{ $message }}</p>
          @enderror

          @foreach ($defaultDateOptions as $option)
          <label class="label @if (str_contains($option['label'], 'UESTC')) has-text-info @endif">{{ $option['label'] }}</label>
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
