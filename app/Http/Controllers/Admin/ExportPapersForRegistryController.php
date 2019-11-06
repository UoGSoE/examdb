<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ExportPapersForRegistry;

class ExportPapersForRegistryController extends Controller
{
    public function store()
    {
        ExportPapersForRegistry::dispatch(request()->user())->onQueue('long-running-queue');

        activity()->causedBy(request()->user())->log("Created a ZIP of the papers for registry");

        return response()->json([
            'message' => 'exporting',
        ]);
    }
}
