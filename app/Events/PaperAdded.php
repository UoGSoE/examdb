<?php

namespace App\Events;

use App\User;
use App\Paper;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PaperAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $paper;

    public $user;

    public function __construct(Paper $paper, User $user)
    {
        $this->paper = $paper;
        $this->user = $user;
    }
}
