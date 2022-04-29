<?php

namespace App\Http\Controllers\Admin;

use App\AcademicSession;
use App\Http\Controllers\Controller;
use App\Scopes\CurrentAcademicSessionScope;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $query = User::orderBy('surname');
        if (request()->withtrashed) {
            $query = $query->withTrashed();
        }

        return view('admin.users.index', [
            'users' => $query->get(),
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

        $data['academic_session_id'] = $request->user()->getCurrentAcademicSession()->id;

        // now manually check for a duplicate _in this academic session_
        $existingUsername = User::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('username', '=', $data['username'])
            ->where('academic_session_id', '=', $data['academic_session_id'])
            ->first();
        $existingEmail = User::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('email', '=', $data['email'])
            ->where('academic_session_id', '=', $data['academic_session_id'])
            ->first();
        if ($existingUsername) {
            $error = ValidationException::withMessages([
                'username' => ['That username already exists'],
            ]);
            throw $error;
        }
        if ($existingEmail) {
            $error = ValidationException::withMessages([
                'email' => ['That email is already taken'],
            ]);
            throw $error;
        }

        $user = User::create($data);

        activity()
            ->causedBy($request->user())
            ->log(
                'Created new '.($user->isExternal() ? 'external' : 'local user')." '{$user->username}'"
            );

        return response()->json([
            'user' => $user,
        ], 201);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(User $user, Request $request)
    {
        $data = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'surname' => 'required',
            'forenames' => 'required',
        ]);

        $data['email'] = strtolower($data['email']);
        if ($user->isExternal()) {
            $data['username'] = $data['email'];
        }

        $user->update($data);

        activity()
            ->causedBy($request->user())
            ->log(
                'Updated details for '.($user->isExternal() ? 'external' : 'local user')." '{$user->username}'"
            );

        return redirect()->route('user.show', $user->id);
    }

    public function destroy(User $user)
    {
        $user->delete();

        activity()
            ->causedBy(request()->user())
            ->log('Disabled '.$user->username);

        return response()->json([
            'message' => 'deleted',
        ]);
    }

    public function reenable($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        activity()
            ->causedBy(request()->user())
            ->log('Re-enabled '.$user->username);

        return response()->json([
            'message' => 'restored',
        ]);
    }
}
