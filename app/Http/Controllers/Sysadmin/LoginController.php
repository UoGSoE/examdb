<?php

namespace App\Http\Controllers\Sysadmin;

use App\Sysadmin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('sysadmin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $credentials['is_sysadmin'] = true; // make laravel's auth check also enforce that is_sysadmin is true on the user record

        if (Auth::guard('sysadmin')->attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('sysadmin.dashboard'));
        }

        return back()->withErrors([
            'auth' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout()
    {
        if (! auth()->check()) {
            return redirect('/');
        }

        auth()->logout();

        return redirect('/');
    }
}
