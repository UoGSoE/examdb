<?php

namespace App\Http\Controllers;

use App\Paper;
use Auth;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home', [
            'moderatedCourses' => auth()->user()
                                    ->courses()
                                    ->wherePivot('is_moderator', true)
                                    ->latest('updated_at')
                                    ->get(),
            'setterCourses' => auth()->user()
                                ->courses()
                                ->wherePivot('is_setter', true)
                                ->latest('updated_at')
                                ->get(),
            'externalCourses' => auth()->user()
                                    ->courses()
                                    ->wherePivot('is_external', true)
                                    ->latest('updated_at')
                                    ->get(),
            'paperList' => auth()->user()
                            ->papers()
                            ->where('subcategory', '!=', Paper::COMMENT_SUBCATEGORY)
                            ->with('course')
                            ->orderByDesc('created_at')
                            ->get(),
        ]);
    }
}
