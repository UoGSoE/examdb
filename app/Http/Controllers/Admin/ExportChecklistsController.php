<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Jobs\BulkExportChecklists;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class ExportChecklistsController extends Controller
{
    public function store(Request $request)
    {
        BulkExportChecklists::dispatch($request->user());

        return redirect()->back();
    }
}
