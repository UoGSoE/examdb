<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GdprUserResource;
use App\Models\User;
use Illuminate\Http\Request;

class GdprExportController extends Controller
{
    public function show(User $user)
    {
        activity()->causedBy(request()->user())->log(
            'GDPR export of '.$user->full_name
        );

        $user->load('papers.comments');

        return new GdprUserResource($user);
    }
}
