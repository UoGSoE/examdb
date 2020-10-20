<?php

namespace App\Http\Livewire;

use App\Course;
use Livewire\Component;

class SemesterEditBox extends Component
{
    public Course $course;

    protected $rules = [
        'course.semester' => 'required|integer|max:3|min:1',
    ];

    public function mount(Course $course)
    {
        $this->course = $course;
    }

    public function render()
    {
        return view('livewire.semester-edit-box');
    }

    public function updatedCourseSemester($value)
    {
        $this->validate();

        $this->course->save();
    }
}
