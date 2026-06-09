<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\NewAccountInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        // Create the account with an unusable random password; the user sets
        // their own via the invitation email and is forced to before sign-in.
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Str::password(32),
            'must_change_password' => true,
        ]);

        $token = Password::broker()->createToken($user);
        $user->notify(new NewAccountInvitation($token));

        return back()->with('status', "Invitation email sent to {$user->email}.");
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        if ($this->wouldRemoveLastAdministrator($user, $validated['role'])) {
            return back()->withErrors(['role' => 'You cannot remove the last administrator.']);
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('status', "Updated role for {$user->email}.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        if ($this->wouldRemoveLastAdministrator($user, UserRole::Manager->value)) {
            return back()->withErrors(['user' => 'You cannot delete the last administrator.']);
        }

        $user->delete();

        return back()->with('status', "User {$user->email} was removed.");
    }

    private function wouldRemoveLastAdministrator(User $user, string $newRole): bool
    {
        return $user->isAdministrator()
            && $newRole !== UserRole::Administrator->value
            && User::where('role', UserRole::Administrator->value)->count() <= 1;
    }
}
