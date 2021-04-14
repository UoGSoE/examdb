<div>
    <h3 class="title is-3">Tenants</h3>
    <form wire:submit.prevent="createNew">
        @csrf
        <div class="field has-addons">
            <div class="control">
              <input class="input" type="text" placeholder="New name..." wire:model.defer="newName">
            </div>
            <div class="control">
                <button class="button is-info" wire:loading.class="loading">Add new school</button>
            </div>
          </div>
    </form>
    <table class="table is-fullwidth is-striped">
        <thead>
            <tr>
                <th>Domain</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tenants as $tenant)
                <tr>
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
                                </div>
                            </form>
                        @else
                            <span>
                                <span>{{ $tenant->domains()->first()->domain }}</span>
                                <button class="button icon" wire:click="editDomain('{{ $tenant->id }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </span>
                        @endif
                    </td>
                    <td>
                        {{ $tenant->created_at->format('d/m/Y H:i') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
