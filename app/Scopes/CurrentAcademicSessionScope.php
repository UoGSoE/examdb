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
        if (! auth()->check()) {
            // we are *probably* running an artisan command or queued job
            // so we don't want to run this scope as we normally would
            return AcademicSession::getDefault();
        }

        $currentSession = auth()->user()->getCurrentAcademicSession();
        if (!$currentSession) {
            abort(500, 'No academic session set');
        }

        $builder->where('academic_session_id', '=', $currentSession->id);
    }
}
