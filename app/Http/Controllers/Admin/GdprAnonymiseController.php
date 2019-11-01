<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GdprAnonymiseController extends Controller
{
    public function store(User $user)
    {
        if ($user->isInternal()) {
            return redirect()->route('user.show', $user->id);
        }

        $user->anonymise();

        activity()->causedBy(request()->user())->log('Anonymised user ' . $user->email);

        return redirect()->route('user.show', $user->id);
    }
}
