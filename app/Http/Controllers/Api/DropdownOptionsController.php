<?php

namespace App\Http\Controllers\Api;

use App\Course;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DropdownOptionsController extends Controller
{
    protected $course;
    protected $papers;

    public function show(Course $course)
    {
        request()->validate([
            'category' => 'required|in:main,resit',
            'subcategory' => 'required|in:main,solution,assessment > 30% (> 25% uestc)',
        ]);

        $this->course = $course;

        if (request('category') == 'main') {
            $this->papers = $course->mainPapers()->get();
        } else {
            $this->papers = $course->resitPapers()->get();
        }

        $options = [];
        if (Auth::user()->isSetterFor($course)) {
            $options = $this->getSetterOptions($options);
        }
        if (Auth::user()->isModeratorFor($course)) {
            $options = array_merge($options, ['Moderator Comments']);
        }
        if (Auth::user()->isExternalFor($course)) {
            $options = ['External Examiner Comments'];
        }

        $subcategory = ucfirst(request('subcategory'));
        $options = collect($options)->map(fn ($option) => $option." ({$subcategory})")->unique()->toArray();

        return [
            'data' => $options,
        ];
    }

    protected function getSetterOptions(array $existingOptions)
    {
        if (
            $this->papers->contains(function ($paper) {
                return Str::startsWith($paper->subcategory, 'Post-Internally Moderated Paper');
            })
        ) {
            return [
                'Post-Internally Moderated Paper',
                'Response To External Examiner',
                'Paper For Registry',
            ];
        }

        if (
            $this->papers->contains(function ($paper) {
                return Str::startsWith($paper->subcategory, 'Moderator Comments');
            })
            or $this->course->hasModeratorChecklist('main')
            or $this->course->hasModeratorChecklist('resit')
        ) {
            return array_merge($existingOptions, [
                'Pre-Internally Moderated Paper',
                'Post-Internally Moderated Paper',
                'Paper For Registry',
            ]);
        }

        return array_merge($existingOptions, ['Pre-Internally Moderated Paper']);
    }
}
