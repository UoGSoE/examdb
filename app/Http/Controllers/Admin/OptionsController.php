<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Appstract\Options\Option;

class OptionsController extends Controller
{
    public function edit()
    {
        return view('admin.options.edit', [
            'options' => Option::all()->flatMap(function ($option) {
                return [$option->key => $option->value];
            }),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'externals_notification_date' => 'nullable|date_format:d/m/Y',
            'teaching_office_contact' => 'nullable|email',
        ]);

        if ($request->filled('externals_notification_date')) {
            option([
                'externals_notification_date' =>
                Carbon::createFromFormat('d/m/Y', $request->externals_notification_date)
                    ->format('Y-m-d')
            ]);
        }

        if ($request->filled('teaching_office_contact')) {
            option([
                'teaching_office_contact' =>
                $request->teaching_office_contact
            ]);
        }

        return response()->json([
            'status' => 'ok'
        ], 200);
    }
}
