<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BulkExportChecklists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExportChecklistsController extends Controller
{
    public function store(Request $request)
    {
        BulkExportChecklists::dispatch($request->user()->id);

        activity()->causedBy($request->user())->log('Created a ZIP of the paper checklists');

        return redirect()->back();
    }
}
