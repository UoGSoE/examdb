<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Sysadmin extends Authenticatable
{
    use HasFactory;
    use CentralConnection;
    use Notifiable;

    protected $casts = [
        'is_sysadmin' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return $this->isSysadmin();
    }

    public function isExternalFor()
    {
        return false;
    }

    public function isImpersonated()
    {
        return false;
    }

    public function isExternal()
    {
        return false;
    }
    public function isSysadmin(): bool
    {
        return $this->is_sysadmin;
    }

    public function isntSysadmin(): bool
    {
        return ! $this->is_sysadmin;
    }

    public function getFullNameAttribute()
    {
        return $this->forenames.' '.$this->surname;
    }

    public function toggleEnabled()
    {
        $this->is_sysadmin = ! $this->is_sysadmin;
        $this->save();
    }
}
