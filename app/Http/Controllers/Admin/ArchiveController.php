<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Paper;
use App\Scopes\CurrentScope;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index()
    {
        return view('admin.archive.index', [
            'papers' => Paper::withoutGlobalScope(CurrentScope::class)->archived()->withoutComments()->orderByDesc('archived_at')->get(),
        ]);
    }
}
