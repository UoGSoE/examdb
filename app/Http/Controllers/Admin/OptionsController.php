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
            'main_deadline_glasgow' => 'nullable|date_format:d/m/Y',
            'main_deadline_uestc' => 'nullable|date_format:d/m/Y',
            'teaching_office_contact_glasgow' => 'nullable|email',
            'teaching_office_contact_uestc' => 'nullable|email',
        ]);

        if ($request->filled('main_deadline_glasgow')) {
            option([
                'main_deadline_glasgow' =>
                Carbon::createFromFormat('d/m/Y', $request->main_deadline_glasgow)
                    ->format('Y-m-d')
            ]);
        }

        if ($request->filled('main_deadline_uestc')) {
            option([
                'main_deadline_uestc' =>
                Carbon::createFromFormat('d/m/Y', $request->main_deadline_uestc)
                    ->format('Y-m-d')
            ]);
        }

        if ($request->filled('teaching_office_contact_glasgow')) {
            option([
                'teaching_office_contact_glasgow' =>
                $request->teaching_office_contact_glasgow
            ]);
        }

        if ($request->filled('teaching_office_contact_uestc')) {
            option([
                'teaching_office_contact_uestc' =>
                $request->teaching_office_contact_uestc
            ]);
        }

        return response()->json([
            'status' => 'ok'
        ], 200);
    }
}
