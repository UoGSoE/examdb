<?php

namespace App\Http\Middleware;

use Closure;

class ApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('x-api-key') != config('exampapers.api_key', 'SETME')) {
            return response()->json('Unauthorized', 401);
        }
        return $next($request);
    }
}
