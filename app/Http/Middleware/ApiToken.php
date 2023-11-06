<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('x-api-key') != config('exampapers.api_key', 'SETME')) {
            return response()->json('Unauthorized', 401);
        }

        return $next($request);
    }
}
