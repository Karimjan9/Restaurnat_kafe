<div class="grid gap-4 md:grid-cols-2">
    <label class="form-control">
        <span class="label-text mb-2 text-slate-300">Name</span>
        <input name="name" value="{{ old('name', $group?->name) }}" class="input input-bordered bg-slate-950/70 text-white" required>
    </label>

    <label class="form-control">
        <span class="label-text mb-2 text-slate-300">Code</span>
        <input name="code" value="{{ old('code', $group?->code) }}" class="input input-bordered bg-slate-950/70 text-white" placeholder="burger_extras">
    </label>
</div>

<div class="grid gap-4 md:grid-cols-4">
    <label class="form-control">
        <span class="label-text mb-2 text-slate-300">Type</span>
        <select name="selection_type" class="select select-bordered bg-slate-950/70 text-white">
            <option value="single" @selected(old('selection_type', $group?->selection_type ?? 'single') === 'single')>Single</option>
            <option value="multiple" @selected(old('selection_type', $group?->selection_type) === 'multiple')>Multiple</option>
        </select>
    </label>

    <label class="form-control">
        <span class="label-text mb-2 text-slate-300">Min</span>
        <input name="min_selected" type="number" min="0" value="{{ old('min_selected', $group?->min_selected ?? 0) }}" class="input input-bordered bg-slate-950/70 text-white" required>
    </label>

    <label class="form-control">
        <span class="label-text mb-2 text-slate-300">Max</span>
        <input name="max_selected" type="number" min="1" value="{{ old('max_selected', $group?->max_selected) }}" class="input input-bordered bg-slate-950/70 text-white">
    </label>

    <label class="form-control">
        <span class="label-text mb-2 text-slate-300">Sort</span>
        <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $group?->sort_order ?? 0) }}" class="input input-bordered bg-slate-950/70 text-white">
    </label>
</div>

<div class="flex flex-wrap gap-4">
    <label class="label cursor-pointer justify-start gap-3">
        <input type="checkbox" name="is_required" value="1" class="checkbox checkbox-warning" @checked(old('is_required', $group?->is_required))>
        <span class="label-text text-slate-300">Required</span>
    </label>

    <label class="label cursor-pointer justify-start gap-3">
        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-warning" @checked(old('is_active', $group?->is_active ?? true))>
        <span class="label-text text-slate-300">Active</span>
    </label>
</div>
