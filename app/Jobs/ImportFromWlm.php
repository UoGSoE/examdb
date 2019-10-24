<?php

namespace App\Jobs;

use App\User;
use App\Wlm\WlmImporter;
use Illuminate\Bus\Queueable;
use App\Wlm\WlmClientInterface;
use App\Events\WlmImportComplete;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportFromWlm implements ShouldQueue
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
    public function handle(WlmClientInterface $client)
    {
        $importer = new WlmImporter($client);

        $importer->run();

        event(new WlmImportComplete($this->user));
    }
}
