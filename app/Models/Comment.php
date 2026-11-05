<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

#[Unguarded]
class Comment extends Model
{
    public function commentable()
    {
        return $this->morphTo();
    }
}
