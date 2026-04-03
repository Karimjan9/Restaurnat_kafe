@if (session('status'))
    <div class="alert mb-4 border border-emerald-400/20 bg-emerald-400/10 text-emerald-50">
        <span>{{ session('status') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="alert mb-4 border border-rose-400/20 bg-rose-400/10 text-rose-50">
        <span>{{ session('error') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="alert mb-4 border border-amber-400/20 bg-amber-400/10 text-amber-50">
        <span>{{ $errors->first() }}</span>
    </div>
@endif
