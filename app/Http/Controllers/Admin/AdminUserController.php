<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $q = trim((string)request('q', ''));
        $role = request('role');
        $status = request('status');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        $query = User::query()
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%' . $q . '%';
                $qq->where(function ($sub) use ($like) {
                    $sub->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->when($role === 'admin', fn($qq) => $qq->where('is_admin', true))
            ->when($role === 'user', fn($qq) => $qq->where('is_admin', false))
            ->when($status === 'locked', fn($qq) => $qq->where('status', 'locked'))
            ->when($status === 'active', function ($qq) {
                $qq->where(function ($sub) {
                    $sub->whereNull('status')->orWhere('status', 'active');
                });
            })
            ->when($dateFrom, fn($qq) => $qq->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($qq) => $qq->whereDate('created_at', '<=', $dateTo))
            ->latest();

        $users = $query->paginate(15)->appends(request()->query());
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $orders = $user->orders()->latest()->paginate(10);
        return view('admin.users.show', compact('user', 'orders'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'sometimes|in:admin,user',
            'is_admin' => 'sometimes|boolean',
            'status' => 'sometimes|in:active,locked',
        ]);

        $dataToUpdate = [];

        if (array_key_exists('role', $validated)) {
            $dataToUpdate['is_admin'] = $validated['role'] === 'admin';
        } elseif (array_key_exists('is_admin', $validated)) {
            $dataToUpdate['is_admin'] = (bool) $validated['is_admin'];
        }

        if (array_key_exists('status', $validated)) {
            $dataToUpdate['status'] = $validated['status'];
        }

        // Prevent admin from locking themselves
        if ($user->id === auth()->id() && (($dataToUpdate['status'] ?? null) === 'locked')) {
            return back()->with('error', 'You cannot lock your own account.');
        }

        if ($dataToUpdate !== []) {
            $user->update($dataToUpdate);
        }

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
