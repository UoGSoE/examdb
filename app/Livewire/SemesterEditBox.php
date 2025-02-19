<?php

namespace App\Livewire;

use App\Models\Course;
use Livewire\Component;

class SemesterEditBox extends Component
{
    public Course $course;
    public array $courseData;

    protected $rules = [
        'courseData.semester' => 'required|integer|max:3|min:1',
    ];

    public function mount(Course $course)
    {
        $this->course = $course;
        $this->courseData = $course->toArray();
    }

    public function render()
    {
        return view('livewire.semester-edit-box');
    }

    public function updatedCourseDataSemester($value)
    {
        $this->validate();

        $this->course->semester = $this->courseData['semester'];
        $this->course->save();
    }
}
