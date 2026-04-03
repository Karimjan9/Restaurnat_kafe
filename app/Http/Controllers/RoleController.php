<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('roles.index', [
            'roles' => Role::with(['permissions', 'users'])->orderBy('label')->get(),
            'permissions' => Permission::orderBy('label')->get(),
            'protectedRoles' => array_keys(config('pos.roles', [])),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:roles,name'],
            'label' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => Str::lower($validated['name']),
            'label' => $validated['label'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return back()->with('status', "Rol qo'shildi.");
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('roles', 'name')->ignore($role->id)],
            'label' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->update([
            'name' => Str::lower($validated['name']),
            'label' => $validated['label'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return back()->with('status', 'Rol yangilandi.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, array_keys(config('pos.roles', [])), true)) {
            return back()->with('error', "Asosiy tizim rollarini o'chirib bo'lmaydi.");
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Bu rolda foydalanuvchilar mavjud.');
        }

        $role->delete();

        return back()->with('status', "Rol o'chirildi.");
    }
}
