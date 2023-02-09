<?php

namespace App\Http\Livewire;

use App\Models\Course;
use Livewire\Component;
use App\Models\Discipline;

class PaperReport extends Component
{
    public $disciplineFilter;
    public $semesterFilter;
    public $categoryFilter;

    protected $queryString = ['disciplineFilter', 'semesterFilter', 'categoryFilter'];

    public function render()
    {
        return view('livewire.paper-report', [
            'courses' => $this->getCourses(),
            'disciplines' => Discipline::orderBy('title')->get(),
        ]);
    }

    public function getCourses()
    {
        return Course::with(['papers', 'setters', 'moderators', 'checklists', 'discipline'])
            ->when($this->disciplineFilter, function ($query) {
                return $query->where('discipline_id', '=', $this->disciplineFilter);
            })
            ->when($this->semesterFilter, function ($query) {
                return $query->where('semester', '=', $this->semesterFilter);
            })
            ->orderBy('code')
            ->get();
    }
}
