<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;

class AdminPermissionController extends Controller
{
    public function update(User $user): JsonResponse
    {
        if ($user->id == request()->user()->id) {
            return response()->json([
                'message' => 'You cant toggle your own status',
            ], 409);
        }

        $user->toggleAdmin();

        return response()->json([
            'user' => $user,
        ]);
    }
}
