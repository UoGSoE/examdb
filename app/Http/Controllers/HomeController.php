<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HomeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
        ];
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
