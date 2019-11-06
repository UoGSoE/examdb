<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportFromWlm;

class WlmImportController extends Controller
{
    public function update()
    {
        ImportFromWlm::dispatch(request()->user());

        activity()->causedBy(request()->user())->log(
            "Started a WLM import"
        );

        return response()->json([
            'message' => 'Imported',
        ], 200);
    }
}
