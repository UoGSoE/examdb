<?php

namespace App\Console\Commands;

use App\AcademicSession;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'examdb:createadmin {username} {email} {forename} {surname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin account';

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
        $session = AcademicSession::getDefault();
        if (! $session) {
            $session = AcademicSession::createFirstSession();
        }

        User::create([
            'username' => $this->argument('username'),
            'password' => bcrypt(Str::random(64)),
            'email' => $this->argument('email'),
            'surname' => $this->argument('surname'),
            'forenames' => $this->argument('forename'),
            'is_staff' => true,
            'is_admin' => true,
            'academic_session_id' => $session->id,
        ]);

        activity()->log('Created admin account '.$this->argument('username').' via the CLI');
    }
}
