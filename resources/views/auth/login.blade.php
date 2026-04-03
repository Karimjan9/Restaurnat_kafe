@extends('layouts.auth')

@section('content')
    <div class="grid w-full gap-6 lg:grid-cols-[1.15fr_0.85fr]">
        <section class="soft-panel rounded-[2rem] border border-white/10 p-8">
            <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Restaurant POS MVP</p>
            <h1 class="mt-4 max-w-xl text-4xl font-semibold text-white">Login, branch flow va checkout bilan ishlaydigan birinchi versiya</h1>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">
                Tizimda admin, manager va cashier rollari tayyor. Branchlar, stollar, kategoriya, mahsulot, order, payment, receipt va basic report oqimi bitta loyihada jamlangan.
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Admin</p>
                    <p class="mt-2 text-sm text-white">admin</p>
                    <p class="text-sm text-slate-400">admin456</p>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Manager</p>
                    <p class="mt-2 text-sm text-white">manager</p>
                    <p class="text-sm text-slate-400">manager456</p>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Cashier</p>
                    <p class="mt-2 text-sm text-white">cashier</p>
                    <p class="text-sm text-slate-400">cashier456</p>
                </div>
            </div>
        </section>

        <section class="soft-panel rounded-[2rem] border border-white/10 p-8">
            <h2 class="text-2xl font-semibold text-white">Accountga kiring</h2>
            <p class="mt-2 text-sm text-slate-400">Role asosida kerakli bo'limga avtomatik yo'naltirilasiz.</p>

            <form action="{{ route('login.store') }}" method="POST" class="mt-8 space-y-4">
                @csrf

                <label class="form-control w-full">
                    <span class="label-text mb-2 text-slate-200">Login</span>
                    <input type="text" name="login" value="{{ old('login') }}" class="input input-bordered w-full bg-slate-950/70 text-white" placeholder="admin" required autofocus>
                </label>

                <label class="form-control w-full">
                    <span class="label-text mb-2 text-slate-200">Parol</span>
                    <input type="password" name="password" class="input input-bordered w-full bg-slate-950/70 text-white" placeholder="admin456" required>
                </label>

                <label class="label cursor-pointer justify-start gap-3">
                    <input type="checkbox" name="remember" value="1" class="checkbox checkbox-warning">
                    <span class="label-text text-slate-300">Eslab qol</span>
                </label>

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-400/20 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">
                        {{ $errors->first() }}
                    </div>
                @endif

                <button type="submit" class="btn btn-warning w-full rounded-2xl">
                    Login
                </button>
            </form>
        </section>
    </div>
@endsection
