<?php

namespace App\Http\Livewire;

use App\Tenant;
use Livewire\Component;

class TenantEditor extends Component
{
    public $newName = '';

    public $editingTenantId = null;

    public $editingDomainName = '';

    public function render()
    {
        return view('livewire.tenant-editor', [
            'tenants' => Tenant::get(),
        ]);
    }

    public function createNew()
    {
        $this->validate([
            'newName' => 'required|string|min:2',
        ]);

        $tenant = Tenant::create(['id' => $this->newName]);
        $tenant->domains()->create(['domain' => $this->getDomainString($this->newName)]);

        $this->newName = '';
    }

    public function editDomain($tenantId)
    {
        $this->editingTenantId = $tenantId;
        $this->editingDomainName = Tenant::find($tenantId)->domains()->first()->domain;
    }

    public function saveDomain()
    {
        $this->validate([
            'editingDomainName' => 'required|string|min:2',
        ]);

        $tenant = Tenant::find($this->editingTenantId);
        $tenant->domains()->first()->update(['domain' => $this->getDomainString($this->editingDomainName)]);

        $this->editingTenantId = null;
        $this->editingDomainName = '';
    }

    protected function getDomainString(string $newDomain): string
    {
        return $newDomain . '.' . parse_url(url('/'), PHP_URL_HOST);
    }
}
