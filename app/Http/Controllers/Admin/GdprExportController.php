<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\GdprUserResource;

class GdprExportController extends Controller
{
    public function show(User $user)
    {
        $user->load('papers.comments');
        return new GdprUserResource($user);
    }
}
