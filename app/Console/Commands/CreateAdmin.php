<?php

namespace App\Console\Commands;

use App\Models\AcademicSession;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('examdb:createadmin {username} {email} {forename} {surname}')]
#[Description('Create an admin account')]
class CreateAdmin extends Command
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
