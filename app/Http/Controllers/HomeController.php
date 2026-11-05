<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
class HomeController extends Controller
{
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
