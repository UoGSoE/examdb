<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaperController extends Controller
{
    public function index()
    {
        return view('admin.papers.index', [
            'courses' => Course::with(['papers', 'setters', 'moderators', 'checklists'])->orderBy('code')->get(),
        ]);
    }
}
