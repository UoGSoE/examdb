<?php

namespace App\Console\Commands;

use App\User;
use App\AcademicSession;
use Illuminate\Console\Command;

class CopyUserToSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'examdb:copy-user-to-session {username} {session? : Eg, "2019/2020"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy a user from the current session into a previous one';

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
        $user = User::where('username', '=', $this->argument('username'))->firstOrFail();
        $sessionName = $this->argument('session');
        if (! $sessionName) {
            $sessionName = $this->choice('Which academic session?', AcademicSession::orderBy('session')->get()->pluck('session')->toArray());
        }
        $session = AcademicSession::where('session', '=', $sessionName)->firstOrFail();

        $existingCopy = User::withoutGlobalScope(CurrentAcademicSessionScope::class)->forAcademicSession($session)->where('username', '=', $user->username)->first();

        if ($existingCopy) {
            $this->error('User ' . $user->username . ' already exists in '.$session->session);
            return Command::FAILURE;
        }

        $copyOfUser = $user->replicate();
        $copyOfUser->academic_session_id = $session->id;
        $copyOfUser->save();

        $this->info('Copy of user "'.$user->username.'" created in session '.$session->session);
        $this->info('Newly created user id is '.$copyOfUser->id);

        return Command::SUCCESS;
    }
}
