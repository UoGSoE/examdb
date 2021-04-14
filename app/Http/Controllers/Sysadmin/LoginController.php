<?php

namespace App\Http\Controllers\Sysadmin;

use App\Http\Controllers\Controller;
use App\Sysadmin;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function show()
    {
        auth()->guard('sysadmin')->login(Sysadmin::first());
    }

    public function login(Request $request)
    {
    }
}
