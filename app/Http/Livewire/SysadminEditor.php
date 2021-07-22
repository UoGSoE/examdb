<?php

namespace App\Http\Livewire;

use App\Sysadmin;
use Livewire\Component;
use Illuminate\Support\Str;

class SysadminEditor extends Component
{
    public $username = '';
    public $surname = '';
    public $forenames = '';
    public $email = '';
    public $ldapErrorMessage = '';

    public function render()
    {
        return view('livewire.sysadmin-editor', [
            'existingAdmins' => Sysadmin::orderBy('surname')->get(),
        ]);
    }

    public function create()
    {
        $this->validate([
            'email' => 'required|email',
            'username' => 'required|string|unique:sysadmins',
            'forenames' => 'required|string',
            'surname' => 'required|string',
        ]);

        Sysadmin::forceCreate([
            'username' => $this->username,
            'email' => $this->email,
            'surname' => $this->surname,
            'forenames' => $this->forenames,
            'is_sysadmin' => true,
            'is_staff' => true,
            'password' => bcrypt(Str::random(64)),
        ]);

        $this->reset();
    }

    public function toggleEnabled($sysadminId)
    {
        if ($sysadminId == auth()->id()) {
            return;
        }
        $admin = Sysadmin::findOrFail($sysadminId);
        $admin->toggleEnabled();
    }

    public function searchForUser()
    {
        if (! $this->username) {
            return;
        }

        try {
            $user = \Ldap::findUser($this->username);
        } catch (\Exception) {
            $this->ldapErrorMessage = 'Could not connect to LDAP.';
            return;
        }
        if (! $user) {
            $this->ldapErrorMessage = 'Could not find that username in LDAP.';
            return;
        }
        $this->email = $user->email;
        $this->surname = $user->surname;
        $this->forenames = $user->forenames;
        $this->ldapErrorMessage = '';
    }
}
