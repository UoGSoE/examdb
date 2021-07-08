<?php

namespace App\Jobs;

use App\User;
use App\Course;
use ZipArchive;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use App\Exporters\ChecklistExporter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use App\Mail\ChecklistsReadyToDownload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BulkExportChecklists implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    protected $paths = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        info('here2');
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        info('here3');
        Course::examined()->get()->each(function ($course) {
            $this->generatePdf($course, 'main');
            $this->generatePdf($course, 'resit');
        });

        $this->zipAllPdfs();

        $downloadLink = URL::temporarySignedRoute(
            'download.checklists',
            now()->addHours(config('exampapers.zip_expire_hours', 12)),
            ['user' => $this->user->id]
        );
        Mail::to($this->user->email)->queue(new ChecklistsReadyToDownload($downloadLink));

        RemoveChecklistZip::dispatch("checklists/checklists_{$this->user->id}.zip")
            ->delay(now()->addHours(config('exampapers.zip_expire_hours', 12)));
    }

    protected function generatePdf(Course $course, string $type): void
    {
        $latestChecklist = $course->checklists()->where('category', '=', $type)->latest()->first();
        if (! $latestChecklist) {
            return;
        }
        $url = URL::temporarySignedRoute('checklist.pdf', now()->addMinutes(5), ['checklist' => $latestChecklist->id]);
        $response = Http::asMultipart()->post(config('exampapers.pdf_api_url'), [
            'remoteURL' => $url,
            'marginTop' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0,
            'marginRight' => 0,
        ]);

        $filename = "checklists/{$this->user->id}/{$course->code}_{$type}_checklist.pdf";
        Storage::disk('local')->put($filename, $response->body());
        $this->paths[] = Storage::disk('local')->path($filename);
    }

    protected function zipAllPdfs(): string
    {
        $localZipname = tempnam(sys_get_temp_dir(), '/'.config('exampapers.checklist_temp_file_prefix'));
        $zip = new \ZipArchive();
        $zip->open($localZipname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($this->paths as $filename) {
            $pdfName = basename($filename);
            $zip->addFile($filename, 'checklists/'.$pdfName);
        }
        $zip->addFromString('ignore_me.txt', '?');
        $zip->close();

        $url = Storage::disk('exampapers')->putFileAs('checklists', $localZipname, "checklists_{$this->user->id}.zip");

        unlink($localZipname);

        return $url;
    }
}
