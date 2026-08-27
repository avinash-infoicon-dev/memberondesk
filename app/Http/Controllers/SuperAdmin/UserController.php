<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->with('business')->latest()->paginate(20);

        return view('super-admin.users.index', compact('users'));
    }

    public function toggle(User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin() && $user->is(auth()->user()), 403);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'User status updated.');
    }
}
