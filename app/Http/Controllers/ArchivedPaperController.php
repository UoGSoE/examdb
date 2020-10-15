<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use App\Scopes\CurrentScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivedPaperController extends Controller
{
    public function show($id)
    {
        $paper = Paper::withoutGlobalScope(CurrentScope::class)->findOrFail($id);
        $this->authorize('view', $paper);

        $encryptedContent = Storage::disk('exampapers')->get($paper->filename);
        $decryptedContent = decrypt($encryptedContent);

        activity()
            ->causedBy(request()->user())
            ->log(
                "Downloaded archived {$paper->category} paper '{$paper->original_filename}' for {$paper->course->code}"
            );

        return response()->streamDownload(function () use ($decryptedContent) {
            echo $decryptedContent;
        }, $paper->original_filename, ['Content-Type', $paper->mimetype]);
    }
}
