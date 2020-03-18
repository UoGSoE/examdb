<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        return view('admin.activity.index', [
            'logs' => Activity::with('causer')->latest()->paginate(100),
        ]);
    }
}
