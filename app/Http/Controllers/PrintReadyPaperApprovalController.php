<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class PrintReadyPaperApprovalController extends Controller
{
    public function update(Paper $paper, Request $request): JsonResponse
    {
        $request->validate([
            'is_approved' => 'required|in:Y,N',
            'comment' => [
                'max:200',
                Rule::requiredIf($request->is_approved === 'N'),
            ],
        ]);

        if (! $request->user()->isSetterFor($paper->course)) {
            abort(Response::HTTP_UNAUTHORIZED, 'Only course setters can approve print ready papers');
        }

        $paper->update([
            'print_ready_approved' => $request->is_approved,
            'print_ready_comment' => $request->comment,
        ]);

        if ($request->is_approved === 'Y') {
            Mail::to($paper->getDisciplineContact())->queue(new \App\Mail\PrintReadyPaperApprovedMail($paper->course));
            activity()->causedBy($request->user())->performedOn($paper->course)->log('Approved print ready paper');
        } else {
            Mail::to($paper->getDisciplineContact())->queue(new \App\Mail\PrintReadyPaperRejectedMail($paper->course, $request->comment ?? ''));
            activity()->causedBy($request->user())->performedOn($paper->course)->log('Rejected print ready paper saying : '.$request->comment);
        }

        return response()->json([
            'papers' => collect([
                'main' => $paper->course->getMainPapers(),
                'resit' => $paper->course->getResitPapers(),
                'resit2' => $paper->course->getResit2Papers(),
            ]),
        ]);
    }
}
