<?php

namespace App\Http\Middleware;

use App\AcademicSession;
use Closure;
use Illuminate\Http\Request;

class AcademicSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->missing('academic_session')) {
            $latestSession = AcademicSession::latest()->first();
            if (! $latestSession) {
                $latestSession = AcademicSession::createForThisYear();
            };
            $request->session()->put('academic_session', $latestSession->session);
        }

        return $next($request);
    }
}
