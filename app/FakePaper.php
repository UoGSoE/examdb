<?php

namespace App;

use App\Scopes\CurrentScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
  This is used to make a paper checklist update show up in the main paper list
  the academics see.  If we just use a Paper model the auto-incrementing id makes
  our fake ID be cast to an int so Vue see's duplicate :key's of '0' or '1'.
  https://stackoverflow.com/a/34603868
  Course::combinePapersAndChecklists()
 */
class FakePaper extends Paper
{
    public $incrementing = false;
}
