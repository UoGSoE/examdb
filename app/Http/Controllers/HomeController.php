<?php

namespace App\Http\Controllers;

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
            'moderatedCourses' => auth()->user()->courses()->wherePivot('is_moderator', true)->get(),
            'setterCourses' => auth()->user()->courses()->wherePivot('is_setter', true)->get(),
            'externalCourses' => auth()->user()->courses()->wherePivot('is_external', true)->get(),
        ]);
    }
}
