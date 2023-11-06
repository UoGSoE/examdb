<?php

namespace App\Http\Middleware;

use App\Models\AcademicSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcademicSessionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->missing('academic_session')) {
            $defaultSession = AcademicSession::getDefault();
            if (! $defaultSession) {
                $defaultSession = AcademicSession::createFirstSession();
            }
            $request->session()->put('academic_session', $defaultSession->session);
        }

        return $next($request);
    }
}
