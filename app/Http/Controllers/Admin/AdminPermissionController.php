<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;

class AdminPermissionController extends Controller
{
    public function update(User $user)
    {
        $user->toggleAdmin();

        return response()->json([
            'user' => $user,
        ]);
    }
}
