<?php

namespace App\Events;

use App\Models\Course;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaperUnapproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $course;

    public $user;

    public $category;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Course $course, User $user, string $category)
    {
        $this->course = $course;
        $this->user = $user;
        $this->category = $category;
    }
}
