<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ImpersonationController extends Controller
{
    public function store(User $user)
    {
        session(['original_id' => auth()->user()->id]);

        activity()
            ->causedBy(auth()->user())
            ->log(
                "Started impersonating {$user->full_name}"
            );

        auth()->login($user);

        return redirect(route('home'));
    }

    public function destroy()
    {
        $originalId = session('original_id');

        if (!$originalId) {
            abort(500);
        }

        $originalUser = User::findOrFail($originalId);

        activity()
            ->causedBy($originalUser)
            ->log(
                "Stopped impersonating " . auth()->user()->full_name
            );

        session()->forget('original_id');

        auth()->loginUsingId($originalId);

        return redirect(route('home'));
    }
}
