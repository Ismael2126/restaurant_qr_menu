<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AuditHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserAdminController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:' . implode(',', array_keys(User::ROLES)),
        ]);

        $user = User::create($validated);

        AuditHelper::log(
            'Create',
            'User',
            'Created staff account: ' . $user->name . ' (' . $user->roleLabel() . ')'
        );

        return back()->with('success', 'Staff account created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:' . implode(',', array_keys(User::ROLES)),
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        AuditHelper::log(
            'Update',
            'User',
            'Updated staff account: ' . $user->name . ' (' . $user->roleLabel() . ')'
        );

        return redirect()->route('admin.users.index')->with('success', 'Staff account updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'user' => 'You cannot delete the account you are currently logged in with.',
            ]);
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors([
                'user' => 'Cannot delete the last admin account.',
            ]);
        }

        $userName = $user->name;

        $user->delete();

        AuditHelper::log('Delete', 'User', 'Deleted staff account: ' . $userName);

        return back()->with('success', 'Staff account deleted.');
    }
}
