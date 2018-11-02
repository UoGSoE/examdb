<?php

namespace App;

use App\Events\PaperApproved;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    protected $guarded = [];

    protected $casts = [
        'setter_approved' => 'boolean',
        'moderator_approved' => 'boolean',
        'external_approved' => 'boolean',
    ];

    public function papers()
    {
        return $this->hasMany(Paper::class);
    }

    public function mainPapers()
    {
        return $this->papers()->main();
    }

    public function resitPapers()
    {
        return $this->papers()->resit();
    }

    public function solutions()
    {
        return $this->hasMany(Paper::class);
    }

    public function mainSolutions()
    {
        return $this->solutions()->main();
    }

    public function resitSolutions()
    {
        return $this->solutions()->resit();
    }

    public function addPaper(string $category, string $subcategory, UploadedFile $file) : Paper
    {
        if (!in_array($category, Paper::VALID_CATEGORIES)) {
            throw new \InvalidArgumentException('Invalid category');
        }

        $randomName = str_random(64);
        $filename = "papers/{$this->id}/{$category}/{$randomName}.dat";
        Storage::disk('exampapers')->put($filename, encrypt($file->get()));
        // $filename = $file->store("papers/{$this->id}/{$category}", 'exampapers');

        return $this->papers()->create([
            'category' => $category,
            'subcategory' => $subcategory,
            'user_id' => auth()->id(),
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mimetype' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function paperApprovedBy(User $user, string $category)
    {
        if ($user->isSetterFor($this)) {
            $this->update(["setter_approved_{$category}" => true]);
            event(new PaperApproved($this, $user, $category));
            return;
        }
        if ($user->isModeratorFor($this)) {
            $this->update(["moderator_approved_{$category}" => true]);
            event(new PaperApproved($this, $user, $category));
            return;
        }
        if ($user->isExternalFor($this)) {
            $this->update(["external_approved_{$category}" => true]);
            event(new PaperApproved($this, $user, $category));
            return;
        }

        throw new \DomainException('User is not associated with this course');
    }

    public function isApprovedBySetter(string $category) : bool
    {
        $key = "setter_approved_{$category}";
        return $this->$key;
    }

    public function isApprovedBy(User $user, string $category) : bool
    {
        if ($user->isSetterFor($this)) {
            $key = "setter_approved_{$category}";
            return $this->$key;
        }
        if ($user->isModeratorFor($this)) {
            $key = "moderator_approved_{$category}";
            return $this->$key;
        }
        if ($user->isSetterFor($this)) {
            $key = "external_approved_{$category}";
            return $this->$key;
        }

        throw new \DomainException('User is not associated with this course');
    }

    public function getFullNameAttribute()
    {
        return $this->code . ' ' . $this->title;
    }
}
