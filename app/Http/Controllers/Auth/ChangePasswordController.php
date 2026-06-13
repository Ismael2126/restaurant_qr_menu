<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\AuditHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChangePasswordController extends Controller
{
    public function show()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        AuditHelper::log('Update', 'Authentication', 'Changed account password.');

        return redirect()
            ->route(match ($user->role) {
                'admin' => 'admin.menu.index',
                default => 'admin.orders.index',
            })
            ->with('success', 'Password updated successfully.');
    }
}
