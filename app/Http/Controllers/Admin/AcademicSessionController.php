<?php

namespace App\Http\Controllers\Admin;

use App\AcademicSession;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\CopyDataToNewAcademicSession;

class AcademicSessionController extends Controller
{
    public function set(AcademicSession $session)
    {
        session(['academic_session' => $session->session]);

        return redirect('/home')->with('success', 'Session changed to ' . $session->session);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'session' => 'required|string|max:255'
        ]);

        $newSession = AcademicSession::create($data);

        CopyDataToNewAcademicSession::dispatch($request->user()->getCurrentAcademicSession(), $newSession, $request()->user());

        return redirect('/home')->with('success', 'Session ' . $newSession->session . ' created.  You will get an email once all the data is copied.');
    }
}
