<?php

namespace App\Jobs;

use App\User;
use App\Jobs\ImportCourseRow;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\CourseImportProcessComplete;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ImportCourseDataBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $spreadsheetData = [];

    public $userId;

    public $academicSessionId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $spreadsheetData, int $userId, int $academicSessionId)
    {
        $this->spreadsheetData = $spreadsheetData;
        $this->userId = $userId;
        $this->academicSessionId = $academicSessionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->userId);
        Bus::batch([])
            ->add(
                collect($this->spreadsheetData)
                    ->map(fn ($row, $rowNumber) => new ImportCourseRow($row, $rowNumber + 1, $this->academicSessionId))
                    ->dump()
                    ->all()
            )
            ->allowFailures()
            ->finally(function ($batch) use ($user) {
                $errors = Redis::smembers($batch->id . '-errors');
                Redis::del($batch->id . '-errors');
                Mail::to($user)->queue(new CourseImportProcessComplete($errors));
            })
            ->dispatch();
    }
}
