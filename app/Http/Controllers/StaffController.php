<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        return view('staff.index', [
            'users' => User::with(['role', 'branch'])->latest()->get(),
            'roles' => Role::orderBy('label')->get(),
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,login'],
            'branch_id' => ['required', 'exists:branches,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        User::create([
            ...$validated,
            'password' => Hash::make($validated['login'].'456'),
        ]);

        return back()->with('status', "Xodim qo'shildi. Parol: {$validated['login']}456");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'login')->ignore($user->id)],
            'branch_id' => ['required', 'exists:branches,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'login' => $validated['login'],
            'branch_id' => $validated['branch_id'],
            'role_id' => $validated['role_id'],
            'password' => $user->login !== $validated['login']
                ? Hash::make($validated['login'].'456')
                : $user->password,
        ]);

        $message = $user->wasChanged('login')
            ? "Xodim ma'lumoti yangilandi. Yangi parol: {$validated['login']}456"
            : "Xodim ma'lumoti yangilandi.";

        return back()->with('status', $message);
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', "O'zingizni o'chirib bo'lmaydi.");
        }

        $user->delete();

        return back()->with('status', "Xodim o'chirildi.");
    }
}
