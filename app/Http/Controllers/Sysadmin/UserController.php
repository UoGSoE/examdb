<?php

namespace App\Http\Controllers\Sysadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show()
    {
        return view('sysadmin.users.edit');
    }
}
