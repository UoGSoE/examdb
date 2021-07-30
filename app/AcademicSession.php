<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    use HasFactory;

    protected $fillable = ['session'];

    public static function createForThisYear()
    {
        if (now()->month < 9) {
            $year = now()->year;
        } else {
            $year = now()->year + 1;
        }

        return static::create([
            'session' => $year . '/' . $year + 1,
        ]);
    }

    public static function findBySession(string $session)
    {
        return static::where('session', '=', $session)->first();
    }
}
