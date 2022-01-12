<?php

namespace App\Http\Controllers\Auth;

use App\AcademicSession;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'username';
    }

    protected function attemptLogin(Request $request)
    {
        if (method_exists($this, 'hasTooManyLoginAttempts') && $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($this->looksLikeStudentAccount($request->username)) {
            info('Attempt to log in using a student account : ' . $request->username);
            $this->incrementLoginAttempts($request);
            abort(Response::HTTP_FORBIDDEN);
        }

        $user = User::where('username', '=', $request->username)->first();
        if (! $user) {
            info('User does not exist in the default session : ' . $request->username);
            $this->incrementLoginAttempts($request);
            throw ValidationException::withMessages([
                'authentication' => 'You have entered an invalid GUID or password',
            ]);
        }

        if (config('ldap.authentication')) {
            if (! \Ldap::authenticate($request->username, $request->password)) {
                $this->incrementLoginAttempts($request);
                throw ValidationException::withMessages([
                    'authentication' => 'You have entered an invalid GUID or password',
                ]);
            }
        }

        Auth::login($user);
        session(['academic_session' => AcademicSession::getDefault()->session]);

        return response(200);
    }

    protected function looksLikeStudentAccount(string $username): bool
    {
        $user = User::where('username', '=', $username)->first();
        if ($user && $user->is_staff) {
            return false;
        }
        return preg_match('/^[0-9].+/', $username) === 1;
    }
}
