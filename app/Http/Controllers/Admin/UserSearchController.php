<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Ohffs\Ldap\LdapService;

class UserSearchController extends Controller
{
    public function show(Request $request, LdapService $ldap)
    {
        $request->validate([
            'guid' => 'required'
        ]);

        $user = $ldap->findUser($request->guid);
        if (!$user) {
            abort(404, 'User not found');
        }

        return response()->json([
            'user' => $user->toArray()
        ]);
    }
}
