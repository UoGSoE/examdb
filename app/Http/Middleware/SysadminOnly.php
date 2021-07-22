<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SysadminOnly
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->isntSysadmin()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
