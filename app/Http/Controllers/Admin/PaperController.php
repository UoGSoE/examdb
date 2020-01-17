<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use App\Discipline;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaperController extends Controller
{
    public function index()
    {
        $query = Course::with(['papers', 'setters', 'moderators', 'checklists', 'discipline'])->orderBy('code');
        if (request()->discipline) {
            $query = $query->where('discipline_id', '=', request()->discipline);
        }

        return view('admin.papers.index', [
            'courses' => $query->get(),
            'disciplines' => Discipline::orderBy('title')->get(),
            'disciplineFilter' => request()->discipline,
        ]);
    }
}
