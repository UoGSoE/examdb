<?php

namespace App\Http\Controllers\Sysadmin;

use App\Events\GlobalSysadminStartedImpersonating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Stancl\Tenancy\Features\UserImpersonation;

class ImpersonationController extends Controller
{
    public function store($token)
    {
        $response = UserImpersonation::makeResponse($token);

        activity()->log('Global Sysadmin started impersonating ' . auth()->user()->full_name);

        GlobalSysadminStartedImpersonating::dispatch(auth()->id());

        return $response;
    }
}
