<?php

namespace App\Http\Controllers;

use App\Tenant;
use Illuminate\Http\Request;

class BaseTenantHomepageController extends Controller
{
    public function show()
    {
        return view('global_home', [
            'tenants' => Tenant::all(),
        ]);
    }
}
