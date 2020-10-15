<?php

namespace App\Jobs;

use App\Events\WlmImportComplete;
use App\Models\User;
use App\Wlm\WlmClientInterface;
use App\Wlm\WlmImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
