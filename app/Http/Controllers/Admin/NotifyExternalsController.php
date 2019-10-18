<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Jobs\NotifyExternals;
use App\Http\Controllers\Controller;

class NotifyExternalsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'area' => 'required|in:glasgow,uestc'
        ]);

        NotifyExternals::dispatch($request->area);

        return response()->json([], 200);
    }
}
