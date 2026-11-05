<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('exampapers:makeadmin {username}')]
#[Description('Make a user an admin')]
class MakeUserAnAdmin extends Command
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::where('username', '=', $this->argument('username'))->firstOrFail();
        $user->makeAdmin();
        activity()->log('Made '.$this->argument('username').' an admin via the CLI');
    }
}
