<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ExportPapersForRegistry;
use Illuminate\Http\Request;

class ExportPapersForRegistryController extends Controller
{
    public function store()
    {
        ExportPapersForRegistry::dispatch(request()->user()->id)->onQueue('long-running-queue');

        activity()->causedBy(request()->user())->log('Created a ZIP of the papers for registry');

        return response()->json([
            'message' => 'exporting',
        ]);
    }
}
