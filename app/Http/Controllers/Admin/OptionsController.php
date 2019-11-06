<?php

namespace App\Http\Controllers\Admin;

use App\Discipline;
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
            'disciplines' => Discipline::orderBy('title')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'external_deadline_glasgow' => 'nullable|date_format:d/m/Y',
            'external_deadline_uestc' => 'nullable|date_format:d/m/Y',
            'internal_deadline_glasgow' => 'nullable|date_format:d/m/Y',
            'internal_deadline_uestc' => 'nullable|date_format:d/m/Y',
            'teaching_office_contact_glasgow' => 'nullable|email',
            'teaching_office_contact_uestc' => 'nullable|email',
        ]);

        if ($request->filled('external_deadline_glasgow')) {
            $date = Carbon::createFromFormat('d/m/Y', $request->external_deadline_glasgow)
                ->format('Y-m-d');
            if ($date != option('external_deadline_glasgow')) {
                option(['teaching_office_notified_externals_glasgow' => 0]);
            }
            option(['external_deadline_glasgow' => $date]);
        }

        if ($request->filled('external_deadline_uestc')) {
            $date = Carbon::createFromFormat('d/m/Y', $request->external_deadline_uestc)
                ->format('Y-m-d');
            if ($date != option('external_deadline_uestc')) {
                option(['teaching_office_notified_externals_uestc' => 0]);
            }
            option(['external_deadline_uestc' => $date]);
        }

        if ($request->filled('internal_deadline_glasgow')) {
            option([
                'internal_deadline_glasgow' =>
                Carbon::createFromFormat('d/m/Y', $request->internal_deadline_glasgow)
                    ->format('Y-m-d')
            ]);
        }

        if ($request->filled('internal_deadline_uestc')) {
            option([
                'internal_deadline_uestc' =>
                Carbon::createFromFormat('d/m/Y', $request->internal_deadline_uestc)
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

        activity()->causedBy($request->user())->log(
            "Updated the site options"
        );

        return response()->json([
            'status' => 'ok'
        ], 200);
    }
}
