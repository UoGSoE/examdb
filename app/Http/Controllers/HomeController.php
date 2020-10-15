<?php

namespace App\Http\Controllers;

use App\Models\Paper;
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
            'moderatedCourses' => auth()->user()->getCourses('is_moderator'),
            'setterCourses' => auth()->user()->getCourses('is_setter'),
            'externalCourses' => auth()->user()->getCourses('is_external'),
            'paperList' => auth()->user()->getAllUploads(),
        ]);
    }
}
