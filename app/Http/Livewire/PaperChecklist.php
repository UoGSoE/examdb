<?php

namespace App\Http\Livewire;

use App\Course;
use App\PaperChecklist as Checklist;
use Livewire\Component;
use Tests\Feature\LivewirePaperChecklistTest;

class PaperChecklist extends Component
{
    public $course;

    public $category;

    public $checklist;

    public $previousId = 'new';

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
        $this->previousId = $checklist->id ?? 'new';
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

    public function showExistingChecklist()
    {
        if (! $this->previousId) {
            return;
        }

        if ($this->previousId === 'new') {
            return redirect()->route('course.checklist.create', [
                'course' => $this->course->id,
                'category' => $this->category,
            ]);
        }

        return redirect()->route('course.checklist.show', $this->previousId);
    }
}
