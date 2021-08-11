<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\CurrentAcademicSessionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discipline extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CurrentAcademicSessionScope);
    }
}
