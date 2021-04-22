<?php

namespace App\Jobs;

use App\Events\WlmImportComplete;
use App\User;
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

    public $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WlmClientInterface $client)
    {
        $this->user = User::find($this->userId);

        $importer = new WlmImporter($client);

        $importer->run();

        event(new WlmImportComplete($this->user));
    }

    public function tags()
    {
        return [
            'tenant:' . tenant('id'),
            'user:' . $this->userId,
        ];
    }
}
