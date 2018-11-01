<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class Course extends Model
{
    protected $guarded = [];

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

        $filename = $file->store("papers/{$this->id}/{$category}", 'exampapers');

        return $this->papers()->create([
            'category' => $category,
            'user_id' => auth()->id(),
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mimetype' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function addSolution($category, UploadedFile $file)
    {
        if (!in_array($category, Paper::VALID_CATEGORIES)) {
            throw new \InvalidArgumentException('Invalid category');
        }

        $filename = $file->store("solutions/{$this->id}/{$category}", 'exampapers');

        return $this->solutions()->create([
            'category' => $category,
            'user_id' => auth()->id(),
            'filename' => $filename,
            'originalFilename' => $file->getClientOriginalName(),
            'mimetype' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function getFullNameAttribute()
    {
        return $this->code . ' ' . $this->title;
    }
}
