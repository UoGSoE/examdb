<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExternalLoginUrl;

class ExternalLoginController extends Controller
{
    public function sendLoginEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users'
        ]);

        $user = User::where('email', '=', $request->email)->firstOrFail();

        Mail::to($user)->queue(new ExternalLoginUrl($user));

        return redirect()->route('home')->with('success', 'Login email has been sent. Please check your email for your login URL.');
    }

    public function login(User $user)
    {
        Auth::login($user);

        return redirect()->route('home');
    }
}
