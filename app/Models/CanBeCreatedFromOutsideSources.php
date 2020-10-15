<?php

namespace App\Models;

use Illuminate\Support\Str;

trait CanBeCreatedFromOutsideSources
{
    /**
     * Create a staff record based on data from the Workload Model.
     */
    public static function staffFromWlmData($wlmStaff)
    {
        $wlmStaff['Username'] = $wlmStaff['GUID'];

        return static::userFromWlmData($wlmStaff, false);
    }

    /**
     * Create a user record based on WLM data.
     */
    protected static function userFromWlmData($wlmData)
    {
        $user = User::findByUsername($wlmData['Username']);
        if (! $user) {
            $user = new static([
                'username' => $wlmData['Username'],
                'email' => $wlmData['Email'],
            ]);
        }
        $user->surname = $wlmData['Surname'] ?? 'Unknown';
        $user->forenames = $wlmData['Forenames'] ?? 'Unknown';
        $user->password = bcrypt(Str::random(32));
        $user->is_staff = true;
        $user->save();

        return $user;
    }
}
