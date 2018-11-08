<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::orderBy('surname')->get(),
        ]);
    }
}
