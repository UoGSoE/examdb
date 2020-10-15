<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class GdprAnonymiseController extends Controller
{
    public function store(User $user)
    {
        if ($user->isInternal()) {
            return redirect()->route('user.show', $user->id);
        }

        $user->anonymise();

        activity()->causedBy(request()->user())->log('Anonymised user '.$user->email);

        return redirect()->route('user.show', $user->id);
    }
}
