<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class MakeUserAnAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exampapers:makeadmin {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a user an admin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $user = User::where('username', '=', $this->argument('username'))->firstOrFail();
        $user->makeAdmin();
        activity()->log("Made " . $this->argument('username') . " an admin via the CLI");
    }
}
