<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::orderBy('surname')->get(),
        ]);
    }

    public function show(User $user)
    {
        $user->load('logs.causer');

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'email' => 'required|email',
            'surname' => 'required',
            'forenames' => 'required',
        ]);

        // if the username is an email address, then user is external to the uni
        $data['is_external'] = strpos($data['username'], '@') !== false;

        // convert usernames & emails to all lower-case
        $data['username'] = strtolower($data['username']);
        $data['email'] = strtolower($data['email']);

        // we don't use password so set to random
        // (saves dealing with any fallout from deleting the password field re.Eloquent etc)
        $data['password'] = bcrypt(Str::random(64));

        $user = User::create($data);

        activity()
            ->causedBy($request->user())
            ->log(
                "Created new " . ($user->isExternal() ? 'external' : 'local user') . " '{$user->username}'"
            );

        return response()->json([
            'user' => $user,
        ], 201);
    }
}
