<?php

namespace App\Scopes;

use App\AcademicSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentAcademicSessionScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // if (auth()->hasUser()) {
        //     $currentSession = auth()->user()->getCurrentAcademicSession();
        if (session()->has('academic_session')) {
            info('PWPWPWP');
            $currentSession = AcademicSession::findBySession(session()->get('academic_session'));
            info('currentSession happy : ' . $currentSession->session);
        } else {
            // we are *probably* running an artisan command, queued job or in the process of logging in
            // so we don't want to run this scope against the auth'd user
            info('WRRRORRORO');
            $currentSession = AcademicSession::getDefault();
            info('currentSession sad : ' . $currentSession->session);
        }

        if (!$currentSession) {
            abort(500, 'No academic session set');
        }

        $builder->where('academic_session_id', '=', $currentSession->id);
    }
}
