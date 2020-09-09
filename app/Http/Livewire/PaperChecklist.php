<?php

namespace App\Http\Livewire;

use App\Course;
use Livewire\Component;
use App\PaperChecklist as Checklist;
use Tests\Feature\LivewirePaperChecklistTest;

class PaperChecklist extends Component
{
    public $course;
    public $category;
    public $checklist;

    public function mount(Course $course, string $category = 'main', $checklist = null)
    {
        $this->course = $course;
        $this->category = $category;
        if (! $checklist) {
            $checklist = $course->getNewChecklist($category);
        }
        $checklist->load('course');
        $checklist->course->append('year');
        $this->checklist = $checklist->toArray();
    }

    public function render()
    {
        return view('livewire.paper-checklist');
    }

    public function save()
    {
        $this->course->addChecklist($this->checklist['fields'], $this->category);

        return redirect()->to(route('course.show', $this->course->id));
    }
}
