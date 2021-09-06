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
use App\Scopes\CurrentAcademicSessionScope;
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
        $user = User::withoutGlobalScope(CurrentAcademicSessionScope::class)->find($this->userId);
        $batch = Bus::batch([]);

        // this works on php 7.4 but breaks on php 8.0 :-/
        // foreach ($this->spreadsheetData as $rowId => $row) {
        //     $batch->add(new ImportCourseRow($row, $rowId + 1, $this->academicSessionId));
        // }

        // this works on php 8.0 but breaks on php 7.4 :-/
        $batch->add(
            collect($this->spreadsheetData)
                ->map(fn ($row, $rowNumber) => new ImportCourseRow($row, $rowNumber + 1, $this->academicSessionId))
                ->all()
        );
        $batch->allowFailures()->finally(function ($batch) use ($user) {
            $errors = Redis::smembers($batch->id . '-errors');
            Redis::del($batch->id . '-errors');
            Mail::to($user)->queue(new CourseImportProcessComplete($errors));
        })->dispatch();
    }
}
