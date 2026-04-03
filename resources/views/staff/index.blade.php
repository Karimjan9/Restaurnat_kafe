@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Role & permission</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">Add staff</h2>

                <form action="{{ route('staff.store') }}" method="POST" class="mt-5 grid gap-4">
                    @csrf

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Full name</span>
                        <input type="text" name="name" class="input input-bordered bg-slate-950/70 text-white" required>
                    </label>

                    <label class="form-control">
                        <span class="label-text mb-2 text-slate-300">Login</span>
                        <input type="text" name="login" class="input input-bordered bg-slate-950/70 text-white" required>
                    </label>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-control">
                            <span class="label-text mb-2 text-slate-300">Role</span>
                            <select name="role_id" class="select select-bordered bg-slate-950/70 text-white" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="form-control">
                            <span class="label-text mb-2 text-slate-300">Branch</span>
                            <select name="branch_id" class="select select-bordered bg-slate-950/70 text-white" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="rounded-2xl border border-amber-400/20 bg-amber-400/10 px-4 py-3 text-sm text-amber-50">
                        Parol avtomatik yaratiladi: <span class="font-semibold">login456</span>
                    </div>

                    <button type="submit" class="btn btn-warning">Add staff</button>
                </form>
            </div>

            <div class="soft-panel rounded-[2rem] border border-white/10 p-6">
                <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Permissions</p>
                <h3 class="mt-2 text-xl font-semibold text-white">Role matrix</h3>

                <div class="mt-5 overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr class="text-slate-400">
                                <th>Permission</th>
                                @foreach ($roles as $role)
                                    <th>{{ $role->label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (config('pos.permissions') as $permission => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    @foreach ($roles as $role)
                                        <td>
                                            @if ($role->permissions->contains('name', $permission))
                                                <span class="badge badge-success badge-outline">Yes</span>
                                            @else
                                                <span class="badge badge-ghost">No</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="soft-panel rounded-[2rem] border border-white/10 p-6">
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Users</p>
            <h3 class="mt-2 text-xl font-semibold text-white">Manage staff accounts</h3>

            <div class="mt-5 space-y-4">
                @foreach ($users as $staff)
                    <form action="{{ route('staff.update', $staff) }}" method="POST" class="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-5">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 xl:grid-cols-[0.9fr_1fr_0.8fr_0.8fr_auto]">
                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Name</span>
                                <input type="text" name="name" value="{{ $staff->name }}" class="input input-bordered bg-slate-950/70 text-white" required>
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Login</span>
                                <input type="text" name="login" value="{{ $staff->login }}" class="input input-bordered bg-slate-950/70 text-white" required>
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Role</span>
                                <select name="role_id" class="select select-bordered bg-slate-950/70 text-white" required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected($staff->role_id === $role->id)>{{ $role->label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="form-control">
                                <span class="label-text mb-2 text-slate-300">Branch</span>
                                <select name="branch_id" class="select select-bordered bg-slate-950/70 text-white" required>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected($staff->branch_id === $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <div class="flex items-end gap-2">
                                <button type="submit" class="btn btn-warning">Save</button>
                                <button form="delete-staff-{{ $staff->id }}" type="submit" class="btn btn-outline btn-error">Delete</button>
                            </div>
                        </div>

                        <p class="mt-4 text-sm text-slate-400">
                            Login o'zgarsa parol ham avtomatik <span class="text-amber-200">yangi-login456</span> formatiga yangilanadi.
                        </p>
                    </form>

                    <form id="delete-staff-{{ $staff->id }}" action="{{ route('staff.destroy', $staff) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </div>
        </section>
    </div>
@endsection
