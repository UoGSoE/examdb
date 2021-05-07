<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\CourseImportProcessComplete;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Redis;

class ImportCourseDataBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $spreadsheetData = [];

    public int $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $spreadsheetData, int $userId)
    {
        $this->spreadsheetData = $spreadsheetData;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $batch = Bus::batch([]);
        $user = User::find($this->userId);
        collect($this->spreadsheetData)->each(fn ($row, $rowNumber) => $batch->add(new ImportCourseRow($row, $rowNumber + 1)));
        $batch->allowFailures()->finally(function ($batch) use ($user) {
            $errors = Redis::smembers($batch->id . '-errors');
            ray('hello');
            Redis::del($batch->id . '-errors');
            Mail::to($user)->queue(new CourseImportProcessComplete($errors));
        })->dispatch();
    }
}
