<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $orders = $user->orders()->latest()->paginate(10);
        return view('admin.users.show', compact('user', 'orders'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'is_admin' => 'boolean',
            'status' => 'in:active,locked',
        ]);

        // Prevent admin from locking themselves
        if ($user->id === auth()->id() && $request->status === 'locked') {
            return back()->with('error', 'You cannot lock your own account.');
        }

        $user->update($request->only(['is_admin', 'status']));

        return back()->with('success', 'User updated successfully.');
    }

    public function lock(Request $request, User $user)
    {
        // Prevent admin from locking themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot lock your own account.');
        }

        $user->lock();

        return back()->with('success', 'User locked successfully.');
    }

    public function unlock(Request $request, User $user)
    {
        $user->unlock();

        return back()->with('success', 'User unlocked successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
