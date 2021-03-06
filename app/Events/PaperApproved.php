<?php

namespace App\Events;

use App\Course;
use App\Paper;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaperApproved
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
