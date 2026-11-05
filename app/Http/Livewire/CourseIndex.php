<?php

namespace App\Http\Livewire;

use App\Models\Course;
use App\Models\Discipline;
use Livewire\Component;

class CourseIndex extends Component
{
    public $disciplineFilter = null;

    public $semesterFilter = null;

    public $includeTrashed = false;

    public $excludeNotExamined = false;

    public $searchTerm = '';

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
            ->when(strlen($this->searchTerm) > 2, fn ($query) => $query->where(
                fn ($query) => $query->where('code', 'like', '%'.$this->searchTerm.'%')->orWhere('title', 'like', '%'.$this->searchTerm.'%')
            ))
            ->when($this->semesterFilter, fn ($query) => $query->where('semester', '=', $this->semesterFilter))
            ->orderBy('code')
            ->get();
    }
}
