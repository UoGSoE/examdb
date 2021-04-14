<?php

namespace App\Http\Controllers\Sysadmin;

use App\Tenant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function show()
    {
        return view('sysadmin.dashboard');
    }
}
