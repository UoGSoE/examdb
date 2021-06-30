<?php

namespace App\Exporters;

use App\User;
use App\Paper;
use Illuminate\Support\Str;
use App\Jobs\RemoveRegistryZip;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

class PaperExporter
{
    public $subcategory;

    public $user;

    public $allFilenames = [];

    public function __construct(string $subcategory, User $user)
    {
        $this->subcategory = $subcategory;
        $this->user = $user;
    }

    public function export(): string
    {
        $papers = Paper::where('subcategory', 'like', $this->subcategory . '%')->get();

        $localZipname = tempnam(sys_get_temp_dir(), '/'.config('exampapers.registry_temp_file_prefix'));
        $zip = new \ZipArchive();
        $zip->open($localZipname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $papers->each(function ($paper) use ($zip) {
            $localFilename = sys_get_temp_dir() . '/' . Str::random(64);
            file_put_contents($localFilename, decrypt(Storage::disk('exampapers')->get($paper->filename)));
            $paperFilename = $paper->course->code.'_'.ucfirst($paper->category).'_'.$paper->original_filename;
            if (in_array($paperFilename, $this->allFilenames)) {
                $paperFilename = $paper->course->code . '_' . ucfirst($paper->category) . '_' . rand(1, 9) . '_' . $paper->original_filename;
            }
            $this->allFilenames[] = $paperFilename;
            $zip->addFile($localFilename, '/papers/'.$paperFilename);
        });
        $zip->addFromString('/papers/tmp.txt', 'Hello');
        $zip->close();
        $remoteFilename = 'registry/papers_'.$this->user->id.'.zip';
        // Storage::disk('exampapers')->put($remoteFilename, encrypt(file_get_contents($localZipname)));
        Storage::disk('exampapers')->put($remoteFilename, file_get_contents($localZipname));
        unlink($localZipname);

        RemoveRegistryZip::dispatch($remoteFilename)
            ->delay(now()->addHours(config('exampapers.zip_expire_hours', 12)));

        return URL::temporarySignedRoute(
            'download.papers.registry',
            now()->addHours(config('exampapers.zip_expire_hours', 12)),
            ['user' => $this->user->id]
        );
    }
}
