<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\User;

class GdprAnonymiseController extends Controller
{
    public function store(User $user): RedirectResponse
    {
        if ($user->isInternal()) {
            return redirect()->route('user.show', $user->id);
        }

        $user->anonymise();

        activity()->causedBy(request()->user())->log('Anonymised user '.$user->email);

        return redirect()->route('user.show', $user->id);
    }
}
