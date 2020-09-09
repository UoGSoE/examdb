<?php

namespace App\Http\Controllers\Api;

use App\Course;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DropdownOptionsController extends Controller
{
    protected $course;
    protected $papers;

    public function show(Course $course)
    {
        request()->validate([
            'category' => 'required|in:main,resit',
            'subcategory' => 'required|in:main,solution,assessment',
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
        $options = collect($options)->map(fn ($option) => $option . " ({$subcategory})")->unique()->toArray();
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
        ) {
            return array_merge($existingOptions, [
                'Pre-Internally Moderated Paper',
                'Post-Internally Moderated Paper'
            ]);
        };

        return array_merge($existingOptions, ['Pre-Internally Moderated Paper']);
    }
}
