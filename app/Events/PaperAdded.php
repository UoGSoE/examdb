<?php

namespace App\Events;

use App\Paper;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
