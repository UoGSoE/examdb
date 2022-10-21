<?php

namespace App\Http\Livewire;

use App\Models\Course;
use App\Models\PaperChecklist as Checklist;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Tests\Feature\LivewirePaperChecklistTest;

class PaperChecklist extends Component
{
    public $course;

    public $category;

    public $checklist;

    public $previousId = 'new';

    public $setters = [];

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
        // get the list of setters - if the current user is a setter, they should be popped at the top of the list
        // so that they are the first/default setter name in the drop-downs for who sets which question
        $setters = $course->setters->sortBy('surname');
        if (auth()->user()->isSetterFor($course)) {
            $setters = $setters->reject(fn ($setter) => $setter->id == auth()->id())->prepend(auth()->user());
        }
        $this->setters = $setters;
    }

    public function render()
    {
        return view('livewire.paper-checklist');
    }

    public function save(?string $sectionName = null)
    {
        if ($sectionName == 'A') {
            Validator::make(
                [ 'date_passed_to_moderator' => $this->checklist['fields']['passed_to_moderator'] ],
                [ 'date_passed_to_moderator' => 'required|date_format:d/m/Y' ]
            )->validate();
        }
        if ($sectionName == 'B') {
        }
        if ($sectionName == 'C') {
        }
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

    public function updating($name, $value)
    {
        if ($name != 'checklist.fields.number_questions') {
            return;
        }
        $validator = Validator::make([ 'number_questions' => $value ], [ 'number_questions' => 'required|integer|min:1' ])->validate();
        foreach (range(1, $value) as $i) {
            if (! array_key_exists('question_setter_' . ($i - 1), $this->checklist['fields'])) {
                $this->checklist['fields']['question_setter_' . ($i - 1)] = auth()->user()->full_name;
            }
        }
    }
}
