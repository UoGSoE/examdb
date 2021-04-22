<div>
    <h3 class="title is-3">Tenants</h3>
    <table class="table is-fullwidth is-striped is-hoverable">
        <thead>
            <tr>
                <th width="5%"></th>
                <th>Domain</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tenants as $tenant)
                <tr>
                    <td>
                        @if ($editingTenantId != $tenant->id)
                            <button class="button icon" wire:click="editDomain('{{ $tenant->id }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                        @endif
                    </td>
                    <td>
                        @if ($editingTenantId == $tenant->id)
                            <form wire:submit.prevent="saveDomain" wire:key="editor-{{ $tenant->id }}">
                                @csrf
                                <div class="field has-addons">
                                    <div class="control">
                                        <input type="text" class="input" wire:model.defer="editingDomainName">
                                    </div>
                                    <div class="control">
                                        <button class="button is-info" wire:click.prevent="saveDomain">
                                            Save
                                        </button>
                                    </div>
                                    @error('editingDomainName')
                                    <div class="control">
                                        <button class="button is-danger" disabled>{{ $message }}</button>
                                    </div>
                                    @enderror
                                </div>
                            </form>
                        @else
                            <span>
                                <span>{{ $tenant->domains()->first()->domain }}</span>
                            </span>
                        @endif
                    </td>
                    <td>
                        {{ $tenant->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <button class="button is-small" wire:click.prevent="loginToTenant('{{ $tenant->id }}')" title="Log into {{ $tenant->id }} as an admin">
                            <span class="icon is-small">
                                <i class="fas fa-user-secret"></i>
                            </span>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <form wire:submit.prevent="createNew">
        @csrf
        <label for="" class="label">New School</label>
        <div class="field has-addons">
            <div class="control">
              <input class="input" type="text" placeholder="New name..." wire:model.lazy="newName">
            </div>
            <div class="control">
                <a class="button is-static">.{{ parse_url(config('app.url'), PHP_URL_HOST) }}</a>
            </div>
        </div>
        @error('tempNewName')
            <p class="has-text-danger">{{ $message }}</p>
        @enderror
        <label for="" class="label">Username (GUID) for initial admin user</label>
        <div class="field has-addons">
            <div class="control">
                <input type="text" class="input" wire:model.defer="newUsername">
            </div>
            <div class="control">
                <button class="button is-info" wire:click.prevent="searchForUser">
                    <span class="icon">
                        <i class="fas fa-search"></i>
                    </span>
                </button>
            </div>
        </div>
        @error('newUsername')
            <p class="has-text-danger">{{ $message }}</p>
        @enderror
        @if($ldapErrorMessage)
            <p class="has-text-danger">{{ $ldapErrorMessage }}</p>
        @endif
        <label for="" class="label">Email</label>
        <div class="field">
            <div class="control"><input type="text" class="input" wire:model.lazy="newEmail"></div>
        </div>
        <label for="" class="label">Surname</label>
        <div class="field">
            <div class="control"><input type="text" class="input" wire:model.lazy="newSurname"></div>
        </div>
        <label for="" class="label">Forenames</label>
        <div class="field">
            <div class="control"><input type="text" class="input" wire:model.lazy="newForenames"></div>
        </div>
        <div class="field">
            <div class="control">
                <button class="button is-info" wire:loading.class="loading" @if (!$this->newSchoolFieldsComplete) disabled @endif>Add new school</button>
            </div>
        </div>
    </form>
</div>
