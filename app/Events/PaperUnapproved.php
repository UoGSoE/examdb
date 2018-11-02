<?php

namespace App\Events;

use App\User;
use App\Course;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

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
