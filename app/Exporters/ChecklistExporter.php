<?php

namespace App\Exporters;

use App\Course;
use App\Jobs\RemoveChecklistZip;
use App\Jobs\RemoveRegistryZip;
use App\Paper;
use App\PaperChecklist;
use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ZipArchive;

class ChecklistExporter
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function export()
    {
        $localZipname = tempnam(sys_get_temp_dir(), '/'.config('exampapers.checklist_temp_file_prefix'));
        $zip = new \ZipArchive();
        $zip->open($localZipname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        Course::each(function ($course) use ($zip) {
            $mainChecklist = PaperChecklist::where('course_id', '=', $course->id)
                ->where('category', '=', 'main')
                ->latest()
                ->first();
            $resitChecklist = PaperChecklist::where('course_id', '=', $course->id)
                ->where('category', '=', 'resit')
                ->latest()
                ->first();
            if ($mainChecklist) {
                $pdf = \PDF::loadView('course.checklist.pdf', ['checklist' => $mainChecklist])->output();
                $pdfName = $course->code.'_'.$mainChecklist->category.'_checklist.pdf';
                $zip->addFromString('/checklists/'.$pdfName, $pdf);
            }
            if ($resitChecklist) {
                $pdf = \PDF::loadView('course.checklist.pdf', ['checklist' => $resitChecklist])->output();
                $pdfName = $course->code.'_'.$resitChecklist->category.'_checklist.pdf';
                $zip->addFromString('/checklists/'.$pdfName, $pdf);
            }
            $zip->addFromString('/checklists/tmp.txt', 'Hello');
        });
        $zip->close();
        $remoteFilename = 'checklists/checklists_'.$this->user->id.'.zip';
        Storage::disk('exampapers')->put($remoteFilename, encrypt(file_get_contents($localZipname)));
        unlink($localZipname);

        RemoveChecklistZip::dispatch($remoteFilename)
            ->delay(now()->addHours(config('exampapers.zip_expire_hours', 12)));

        return URL::temporarySignedRoute(
            'download.checklists',
            now()->addHours(config('exampapers.zip_expire_hours', 12)),
            ['user' => $this->user->id]
        );
    }
}
