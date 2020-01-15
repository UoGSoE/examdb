<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DownloadChecklistsController extends Controller
{
    public function show(User $user, Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(401);
        }

        if (Gate::denies('download_registry', $user)) {
            abort(401);
        }

        $decryptedContent = decrypt(Storage::disk('exampapers')->get('checklists/checklists_' . $user->id . '.zip'));

        activity()
            ->causedBy($user)
            ->log(
                "Downloaded paper checklists ZIP"
            );

        return response()->streamDownload(function () use ($decryptedContent) {
            echo $decryptedContent;
        }, 'paper_checklists_' . now()->format('d_m_Y_H_i') . '.zip', ['content-type' => 'application/zip']);
    }
}
