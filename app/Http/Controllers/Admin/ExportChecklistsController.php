<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Jobs\BulkExportChecklists;
use Illuminate\Http\Request;

class ExportChecklistsController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        BulkExportChecklists::dispatch($request->user())->onQueue('long-running-queue');

        activity()->causedBy($request->user())->log('Created a ZIP of the paper checklists');

        return redirect()->back();
    }
}
