<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Discipline;
use Illuminate\Http\Request;

class DisciplineContactController extends Controller
{
    public function update(Request $request): JsonResponse
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
