<?php

namespace App\Http\Livewire;

use App\Course;
use Livewire\Component;
use App\PaperChecklist as Checklist;

class PaperChecklist extends Component
{
    public $course;
    public $category;
    public $checklist;

    public function mount(Course $course, string $category = 'main')
    {
        $this->course = $course;
        $this->category = $category;
        $checklist = new Checklist([
            'course_id' => $course->id,
            'category' => $category,
            'version' => Checklist::CURRENT_VERSION,
        ]);
        $checklist->load('course');
        $checklist->course->append('year');
        $this->checklist = $checklist->toArray();
    }

    public function render()
    {
        return view('livewire.paper-checklist');
    }
}
