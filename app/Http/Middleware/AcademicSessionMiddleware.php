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
        info('MIDDLE: ' . $request->session()->get('academic_session'));
        if ($request->session()->missing('academic_session')) {
            $defaultSession = AcademicSession::getDefault();
            if (! $defaultSession) {
                $defaultSession = AcademicSession::createFirstSession();
            };
            $request->session()->put('academic_session', $defaultSession->session);
        }

        return $next($request);
    }
}
