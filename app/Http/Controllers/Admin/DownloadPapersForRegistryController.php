<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DownloadPapersForRegistryController extends Controller
{
    public function show(User $user, Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        if (Gate::denies('download_registry', $user)) {
            abort(401);
        }

        $decryptedContent = decrypt(Storage::disk('exampapers')->get('registry/papers_'.$user->id.'.zip'));

        activity()
            ->causedBy($user)
            ->log(
                'Downloaded papers for registry ZIP'
            );

        return response()->streamDownload(function () use ($decryptedContent) {
            echo $decryptedContent;
        }, 'papers_for_registry_'.now()->format('d_m_Y_H_i').'.zip', ['content-type' => 'application/zip']);
    }
}
