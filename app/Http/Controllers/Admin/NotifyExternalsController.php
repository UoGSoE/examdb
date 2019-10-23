<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Jobs\NotifyExternals;
use App\Http\Controllers\Controller;

class NotifyExternalsController extends Controller
{
    public function show()
    {
        return view('admin.email_externals');
    }

    public function store(Request $request)
    {
        $request->validate([
            'area' => 'required|in:glasgow,uestc'
        ]);

        NotifyExternals::dispatch($request->area);

        activity()
            ->causedBy($request->user())
            ->log("Notified externals for " . ucfirst($request->area));

        if ($request->wantsJson()) {
            return response()->json([], 200);
        }

        return redirect()->route('paper.index');
    }
}
