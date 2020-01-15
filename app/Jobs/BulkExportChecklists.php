<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use App\Exporters\ChecklistExporter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use App\Mail\ChecklistsReadyToDownload;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BulkExportChecklists implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $link = (new ChecklistExporter($this->user))->export();

        Mail::to($this->user->email)->queue(new ChecklistsReadyToDownload($link));
    }
}
