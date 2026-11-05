<?php

namespace App\Models;

use App\Scopes\CurrentAcademicSessionScope;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Unguarded]
class Discipline extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CurrentAcademicSessionScope);
    }
}
