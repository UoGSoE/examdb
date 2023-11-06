<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Scopes\CurrentAcademicSessionScope;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        return view('admin.activity.index', [
            'logs' => Activity::with(['causer' => fn ($query) => $query->withoutGlobalScope(CurrentAcademicSessionScope::class)])->latest()->paginate(100),
        ]);
    }
}
