<?php

namespace App\Jobs;

use App\Exporters\PaperExporter;
use App\Jobs\RemoveRegistryZip;
use App\Mail\RegistryPapersExported;
use App\Paper;
use App\User;
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

    public $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $userId)
    {
        ray($userId);
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->user = User::find($this->userId);

        $link = (new PaperExporter(Paper::PAPER_FOR_REGISTRY, $this->user))->export();

        Mail::to($this->user->email)->queue(new RegistryPapersExported($link));
    }

    public function tags()
    {
        return [
            'tenant:' . tenant('id'),
            'user:' . $this->userId,
        ];
    }
}
