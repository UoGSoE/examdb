<?php

namespace App;

use Illuminate\Support\Str;
use Ohffs\Ldap\LdapUser;

trait CanBeCreatedFromOutsideSources
{
    public static function createFromLdap(LdapUser $ldapUser, $academicSessionId = null)
    {
        return static::create([
            'username' => $ldapUser->username,
            'email' => $ldapUser->email,
            'surname' => $ldapUser->surname,
            'forenames' => $ldapUser->forenames,
            'is_staff' => true,
            'password' => bcrypt(Str::random(64)),
            'academic_session_id' => $academicSessionId ?? AcademicSession::getDefault()->id,
        ]);
    }
}
