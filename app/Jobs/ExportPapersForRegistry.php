<?php

namespace App\Jobs;

use App\User;
use App\Paper;
use Illuminate\Bus\Queueable;
use App\Jobs\RemoveRegistryZip;
use App\Exporters\PaperExporter;
use App\Mail\RegistryPapersExported;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExportPapersForRegistry implements ShouldQueue
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
        $link = (new PaperExporter(Paper::PAPER_FOR_REGISTRY, $this->user))->export();

        Mail::to($this->user->email)->queue(new RegistryPapersExported($link));
    }
}
