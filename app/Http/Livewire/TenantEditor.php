<?php

namespace App\Http\Livewire;

use App\User;
use App\Tenant;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantEditor extends Component
{
    public $newName = '';
    public $newUsername = '';
    public $newEmail = '';
    public $newForenames = '';
    public $newSurname = '';

    public $tempNewName = '';

    public $editingTenantId = null;

    public $editingDomainName = '';

    public $ldapErrorMessage = '';

    public function render()
    {
        return view('livewire.tenant-editor', [
            'tenants' => Tenant::get(),
        ]);
    }

    public function createNew()
    {
        $this->tempNewName = $this->getDomainString($this->newName);
        $this->validate([
            'tempNewName' => 'required|string|min:2|unique:domains,domain',
            'newEmail' => 'required|email',
            'newUsername' => 'required|string',
            'newForenames' => 'required|string',
            'newSurname' => 'required|string',
        ]);

        // TODO when creating the tenant - set the initial guid rather than this inline-create-user
        // use the BootstrapNewTenant to pull the guid out and create the user then - that way
        // it happens after the database tables etc have all been created.
        $tenant = Tenant::create(['id' => $this->newName]);
        $tenant->domains()->create(['domain' => $this->getDomainString($this->newName)]);
        $tenant->run(function ($tenant) {
            User::create([
                'username' => strtolower(trim($this->newUsername)),
                'email' => $this->newEmail,
                'password' => bcrypt(Str::random(64)),
                'forenames' => $this->newForenames,
                'surname' => $this->newSurname,
                'is_admin' => true,
                'is_staff' => true,
            ]);
        });
        $this->newName = '';
        $this->newUsername = '';
        $this->newEmail = '';
        $this->newForenames = '';
        $this->newSurname = '';
    }

    public function editDomain($tenantId)
    {
        $this->editingTenantId = $tenantId;
        $this->editingDomainName = Tenant::find($tenantId)->domains()->first()->domain;
    }

    public function saveDomain()
    {
        $domainId = Tenant::findOrFail($this->editingTenantId)->domains()->first()->id;
        $this->validate([
            'editingDomainName' => 'required|string|min:2|unique:domains,domain,' . $domainId . ',id',
        ]);

        $tenant = Tenant::find($this->editingTenantId);
        $tenant->domains()->first()->update(['domain' => $this->getDomainString(explode('.', $this->editingDomainName)[0])]);

        $this->editingTenantId = null;
        $this->editingDomainName = '';
    }

    protected function getDomainString(string $newDomain): string
    {
        return $newDomain . '.' . parse_url(url('/'), PHP_URL_HOST);
    }

    public function searchForUser()
    {
        if (! $this->newUsername) {
            return;
        }

        try {
            $user = \Ldap::findUser($this->newUsername);
        } catch (\Exception $e) {
            $this->ldapErrorMessage = 'Could not connect to LDAP.';
            return;
        }
        if (! $user) {
            $this->ldapErrorMessage = 'Could not find that username in LDAP.';
            return;
        }
        $this->newEmail = $user->email;
        $this->newSurname = $user->surname;
        $this->newForenames = $user->forenames;
        $this->ldapErrorMessage = '';
    }

    public function getNewSchoolFieldsCompleteProperty()
    {
        return (bool) $this->newName &&
            $this->newUsername &&
            $this->newEmail &&
            $this->newSurname &&
            $this->newForenames;
    }
}
