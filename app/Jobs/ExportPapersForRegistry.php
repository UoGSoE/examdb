<?php

namespace App\Jobs;

use App\Exporters\PaperExporter;
use App\Jobs\RemoveRegistryZip;
use App\Mail\RegistryPapersExported;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

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
