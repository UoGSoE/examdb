<?php

namespace App;

use Ohffs\Ldap\LdapUser;
use Illuminate\Support\Str;

trait CanBeCreatedFromOutsideSources
{
    public static function createFromLdap(LdapUser $ldapUser)
    {
        return static::create([
            'username' => $ldapUser->username,
            'email' => $ldapUser->email,
            'surname' => $ldapUser->surname,
            'forenames' => $ldapUser->forenames,
            'is_staff' => true,
            'password' => bcrypt(Str::random(64)),
            'academic_session_id' => AcademicSession::getDefault()->id,
        ]);
    }
}
