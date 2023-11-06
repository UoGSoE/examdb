<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RemoveRegistryZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filename;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Storage::disk('exampapers')->delete($this->filename);
        activity()->log('Automatically removed registry zip '.$this->filename);
    }
}
