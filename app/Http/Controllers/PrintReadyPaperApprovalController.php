<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class PrintReadyPaperApprovalController extends Controller
{
    public function update(Paper $paper, Request $request)
    {
        $request->validate([
            'is_approved' => 'required|boolean',
            'comment' => [
                'max:200',
                Rule::requiredIf($request->is_approved === false),
            ],
        ]);

        if (! $request->user()->isSetterFor($paper->course)) {
            abort(Response::HTTP_UNAUTHORIZED, 'Only course setters can approve print ready papers');
        }

        $paper->update([
            'print_ready_approved' => $request->is_approved,
            'print_ready_comment' => $request->comment,
        ]);

        return response()->json([
            'papers' => collect([
                'main' => $paper->course->mainPapers()->with(['user', 'comments'])->latest()->get(),
                'resit' => $paper->course->resitPapers()->with(['user', 'comments'])->latest()->get(),
                'resit2' => $paper->course->resit2Papers()->with(['user', 'comments'])->latest()->get(),
            ]),
        ]);
    }
}
