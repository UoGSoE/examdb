<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    use HasFactory;

    protected $fillable = ['session', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public static function createFirstSession(): AcademicSession
    {
        $year = now()->year;

        return static::create([
            'session' => $year . '/' . ($year + 1),
            'is_default' => true,
        ]);
    }

    public static function getDefault()
    {
        return static::where('is_default', '=', true)->first();
    }

    public function setAsDefault()
    {
        AcademicSession::all()->each(fn ($session) => $session->update(['is_default' => false]));
        $this->update(['is_default' => true]);
    }

    public static function findBySession(string $session)
    {
        return static::where('session', '=', $session)->first();
    }
}
