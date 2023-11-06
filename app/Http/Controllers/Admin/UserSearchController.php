<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ohffs\Ldap\LdapService;

class UserSearchController extends Controller
{
    public function show(Request $request, LdapService $ldap): JsonResponse
    {
        $request->validate([
            'guid' => 'required',
        ]);

        $user = $ldap->findUser($request->guid);
        if (! $user) {
            abort(404, 'User not found');
        }

        return response()->json([
            'user' => $user->toArray(),
        ]);
    }
}
