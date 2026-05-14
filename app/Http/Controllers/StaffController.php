<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        $temporaryPassword = $this->generateTemporaryPassword();

        $user = User::create([
            ...$validated,
            'password' => Hash::make($temporaryPassword),
            'force_password_change' => true,
        ]);

        Audit::record('staff.created', $user, [
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'role_id' => $user->role_id,
        ], $request);

        return back()->with('status', "Xodim qo'shildi. Vaqtinchalik parol: {$temporaryPassword}");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'login')->ignore($user->id)],
            'branch_id' => ['required', 'exists:branches,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $loginChanged = $user->login !== $validated['login'];
        $temporaryPassword = $loginChanged ? $this->generateTemporaryPassword() : null;

        $user->update([
            'name' => $validated['name'],
            'login' => $validated['login'],
            'branch_id' => $validated['branch_id'],
            'role_id' => $validated['role_id'],
            'password' => $temporaryPassword
                ? Hash::make($temporaryPassword)
                : $user->password,
            'force_password_change' => $temporaryPassword ? true : $user->force_password_change,
        ]);

        Audit::record('staff.updated', $user, [
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'role_id' => $user->role_id,
            'login_changed' => $loginChanged,
        ], $request);

        $message = $temporaryPassword
            ? "Xodim ma'lumoti yangilandi. Vaqtinchalik parol: {$temporaryPassword}"
            : "Xodim ma'lumoti yangilandi.";

        return back()->with('status', $message);
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', "O'zingizni o'chirib bo'lmaydi.");
        }

        Audit::record('staff.deleted', $user, [
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'role_id' => $user->role_id,
        ]);

        $user->delete();

        return back()->with('status', "Xodim o'chirildi.");
    }

    protected function generateTemporaryPassword(): string
    {
        return 'Tmp-'.Str::upper(Str::random(4)).'-'.random_int(1000, 9999);
    }
}
