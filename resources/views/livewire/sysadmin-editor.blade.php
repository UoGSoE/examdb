<div>
    <h3 class="title is-3">Existing Sysadmins</h3>
    <table class="table is-fullwidth is-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Username</th>
                <th>Enabled?</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($existingAdmins as $admin)
                <tr wire:key="sysadmin-{{ $admin->id }}">
                    <td>{{ $admin->full_name }}</td>
                    <td>{{ $admin->email }}</td>
                    <td>{{ $admin->username }}</td>
                    <td>
                        @if ($admin->id != auth()->id())
                            <button class="button is-white" wire:click="toggleEnabled({{ $admin->id }})" title="Toggle enabled/disabled">
                                <span class="icon">
                                    @if ($admin->isSysadmin())
                                        <i class="fas fa-check-circle"></i>
                                    @else
                                        <i class="fas fa-times-circle has-text-danger"></i>
                                    @endif
                                </span>
                            </button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <form wire:submit.prevent="create">
        @csrf
        <label for="" class="label">Username (GUID) for new sysadmin</label>
        <div class="field has-addons">
            <div class="control">
                <input type="text" class="input" wire:model.defer="username">
            </div>
            <div class="control">
                <button class="button is-info" wire:click.prevent="searchForUser">
                    <span class="icon">
                        <i class="fas fa-search"></i>
                    </span>
                </button>
            </div>
        </div>
        @error('username')
            <p class="has-text-danger">{{ $message }}</p>
        @enderror
        @if($ldapErrorMessage)
            <p class="has-text-danger">{{ $ldapErrorMessage }}</p>
        @endif
        <label for="" class="label">Email</label>
        <div class="field">
            <div class="control"><input type="text" class="input" wire:model.lazy="email"></div>
        </div>
        <label for="" class="label">Surname</label>
        <div class="field">
            <div class="control"><input type="text" class="input" wire:model.lazy="surname"></div>
        </div>
        <label for="" class="label">Forenames</label>
        <div class="field">
            <div class="control"><input type="text" class="input" wire:model.lazy="forenames"></div>
        </div>
        <div class="field">
            <div class="control">
                <button class="button is-info" wire:loading.class="loading">Add new sysadmin</button>
            </div>
        </div>
    </form>
</div>
