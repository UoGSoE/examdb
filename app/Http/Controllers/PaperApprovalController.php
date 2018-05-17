<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Paper;

class PaperApprovalController extends Controller
{
    public function store(Paper $paper)
    {
        $paper->setterApproves();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Paper Approved',
            ]);
        }

        return redirect()->route('course.show', $paper->course_id);
    }
}
