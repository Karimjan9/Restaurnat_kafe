@extends('layouts.auth')

@section('content')
    <section class="soft-panel mx-auto w-full max-w-xl rounded-[2rem] border border-white/10 p-8">
        <p class="text-xs uppercase tracking-[0.35em] text-amber-200">Password update</p>
        <h1 class="mt-3 text-3xl font-semibold text-white">Vaqtinchalik parolni almashtiring</h1>
        <p class="mt-3 text-sm leading-7 text-slate-300">
            Xavfsizlik uchun yangi xodim yoki reset qilingan account birinchi kirishda yangi parol o'rnatishi kerak.
        </p>

        @if (session('warning'))
            <div class="mt-5 rounded-2xl border border-amber-400/20 bg-amber-400/10 px-4 py-3 text-sm text-amber-50">
                {{ session('warning') }}
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <label class="block">
                <span class="mb-2 block text-sm text-slate-300">Joriy parol</span>
                <input type="password" name="current_password" required autocomplete="current-password" class="input input-bordered w-full rounded-2xl bg-slate-950/70 text-white">
                @error('current_password') <span class="mt-2 block text-sm text-rose-300">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="mb-2 block text-sm text-slate-300">Yangi parol</span>
                <input type="password" name="password" required autocomplete="new-password" class="input input-bordered w-full rounded-2xl bg-slate-950/70 text-white">
                @error('password') <span class="mt-2 block text-sm text-rose-300">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="mb-2 block text-sm text-slate-300">Yangi parolni tasdiqlash</span>
                <input type="password" name="password_confirmation" required autocomplete="new-password" class="input input-bordered w-full rounded-2xl bg-slate-950/70 text-white">
            </label>

            <button type="submit" class="btn btn-warning w-full rounded-2xl">Parolni yangilash</button>
        </form>
    </section>
@endsection
