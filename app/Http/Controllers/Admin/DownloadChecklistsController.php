<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DownloadChecklistsController extends Controller
{
    public function show(User $user, Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        if (Gate::denies('download_registry', $user)) {
            abort(401);
        }

        activity()->causedBy($user)->log('Downloaded paper checklists ZIP');

        $contents = Storage::disk('exampapers')->get('checklists/checklists_'.$user->id.'.zip');

        return response()->streamDownload(function () use ($contents) {
            echo $contents;
        }, 'paper_checklists_'.now()->format('d_m_Y_H_i').'.zip', ['content-type' => 'application/zip']);
    }
}
