<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ExportPapersForRegistry;
use Illuminate\Http\JsonResponse;

class ExportPapersForRegistryController extends Controller
{
    public function store(): JsonResponse
    {
        ExportPapersForRegistry::dispatch(request()->user())->onQueue('long-running-queue');

        activity()->causedBy(request()->user())->log('Created a ZIP of the papers for registry');

        return response()->json([
            'message' => 'exporting',
        ]);
    }
}
