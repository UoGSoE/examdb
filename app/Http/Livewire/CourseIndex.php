<?php

namespace App\Http\Livewire;

use App\Course;
use App\Discipline;
use Livewire\Component;

class CourseIndex extends Component
{
    public $disciplineFilter = null;

    public $includeTrashed = false;
    public $excludeNotExamined = false;

    public function render()
    {
        return view('livewire.course-index', [
            'courseList' => $this->getFilteredCourses(),
            'disciplines' => Discipline::orderBy('title')->get(),
        ]);
    }

    public function getFilteredCourses()
    {
        return Course::with(['setters', 'moderators', 'externals', 'discipline', 'checklists'])
            ->when($this->disciplineFilter, fn ($query) => $query->where('discipline_id', '=', $this->disciplineFilter))
            ->when($this->includeTrashed, fn ($query) => $query->withTrashed())
            ->when($this->excludeNotExamined, fn ($query) => $query->where('is_examined', '=', '1'))
            ->orderBy('code')
            ->get();
    }
}
