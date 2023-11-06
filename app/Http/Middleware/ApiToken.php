<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;

class ApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('x-api-key') != config('exampapers.api_key', 'SETME')) {
            return response()->json('Unauthorized', 401);
        }

        return $next($request);
    }
}
