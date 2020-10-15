<?php

namespace App\Http\Controllers\Admin;

use App\Models\Discipline;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DisciplineContactController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'disciplines.*.contact' => 'required|email',
            'disciplines.*.id' => 'required|integer',
        ]);

        collect($request->disciplines)->each(function ($entry) {
            $discipline = Discipline::findOrFail($entry['id']);
            $discipline->update(['contact' => $entry['contact']]);
        });

        return response()->json([
            'message' => 'updated',
        ]);
    }
}
