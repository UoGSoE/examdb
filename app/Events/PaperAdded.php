<?php

namespace App\Events;

use App\Models\Paper;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
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
