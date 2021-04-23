<?php

namespace App\Console\Commands;

use App\Sysadmin;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class MakeSysadmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'examdb:makesysadmin {username} {email} {forename} {surname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new global sysadmin who can manage tenants';

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
     * @return int
     */
    public function handle()
    {
        Sysadmin::forceCreate([
            'username' => $this->argument('username'),
            'email' => $this->argument('email'),
            'surname' => $this->argument('surname'),
            'forenames' => $this->argument('forename'),
            'is_sysadmin' => true,
            'is_staff' => true,
            'password' => bcrypt(Str::random(64)),
        ]);
    }
}
