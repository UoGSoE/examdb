<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Discipline;

class PaperController extends Controller
{
    public function index(): View
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
