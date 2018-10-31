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
            'email' => 'required|email'
        ]);

        $user = User::where('username', '=', strtolower($request->email))->first();

        if ($user) {
            Mail::to($user)->queue(new ExternalLoginUrl($user));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'Login attempted'
            ], 200);
        }

        return redirect()->route('home')->with('success', 'Login email has been sent. Please check your email for your login URL.');
    }

    public function login(User $user)
    {
        Auth::login($user);

        return redirect()->route('home');
    }
}
