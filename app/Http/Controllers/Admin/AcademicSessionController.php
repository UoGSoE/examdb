<?php

namespace App\Http\Controllers\Admin;

use App\Models\AcademicSession;
use App\Http\Controllers\Controller;
use App\Jobs\CopyDataToNewAcademicSession;
use App\Scopes\CurrentAcademicSessionScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AcademicSessionController extends Controller
{
    public function set(AcademicSession $session, Request $request)
    {
        $username = $request->user()->username;

        auth()->logout();

        // we set the session twice - first time to get the user scoped to the session they have chosen, then log them in as that
        // 'version' of their user which in turn clears the session so we need to set it again
        session(['academic_session' => $session->session]);
        auth()->login(
            \App\Models\User::where('username', '=', $username)->first()
        );
        session(['academic_session' => $session->session]);

        return redirect('/home')->with('success', 'Session changed to '.$session->session);
    }

    public function edit()
    {
        return view('admin.academicsessions.edit', [
            'academicSessions' => AcademicSession::orderBy('session')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $session = $request->new_session_year_1.'/'.$request->new_session_year_2;
        $request->merge(['session_name' => $session]);
        $request->validate([
            'new_session_year_1' => 'required|integer|min:2010|max:2050',
            'new_session_year_2' => 'required|integer|min:2010|max:2050|different:new_session_year_1',
            'session_name' => 'required|unique:academic_sessions,session',
        ]);

        $newSession = AcademicSession::create(['session' => $session]);

        CopyDataToNewAcademicSession::dispatch(AcademicSession::getDefault(), $newSession, $request->user());

        Cache::forget('navbarAcademicSessions');

        activity()->causedBy($request->user())->log('Created a new academic session '.$session);

        return redirect('/home')->with('success', 'Session '.$newSession->session.' created.  You will get an email once all the data is copied.');
    }

    public function setDefault(AcademicSession $session, Request $request)
    {
        $session->setAsDefault();

        activity()->causedBy($request->user())->log('Changed the default academic session to '.$session->session);

        return redirect()->back();
    }
}
