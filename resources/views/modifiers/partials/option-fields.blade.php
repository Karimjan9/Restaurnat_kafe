<div class="grid gap-3 md:grid-cols-[1fr_0.8fr_0.7fr_0.7fr_0.55fr]">
    <label class="form-control">
        <span class="label-text mb-2 text-slate-300">Option</span>
        <input name="name" value="{{ old('name', $option?->name) }}" class="input input-sm input-bordered bg-slate-950/70 text-white" required>
    </label>

    <label class="form-control">
        <span class="label-text mb-2 text-slate-300">Code</span>
        <input name="code" value="{{ old('code', $option?->code) }}" class="input input-sm input-bordered bg-slate-950/70 text-white">
    </label>

    <label class="form-control">
        <span class="label-text mb-2 text-slate-300">Price +</span>
        <input name="price_delta" type="number" step="0.01" value="{{ old('price_delta', $option?->price_delta ?? 0) }}" class="input input-sm input-bordered bg-slate-950/70 text-white">
    </label>

    <label class="form-control">
        <span class="label-text mb-2 text-slate-300">Cost +</span>
        <input name="cost_delta" type="number" step="0.01" value="{{ old('cost_delta', $option?->cost_delta ?? 0) }}" class="input input-sm input-bordered bg-slate-950/70 text-white">
    </label>

    <label class="form-control">
        <span class="label-text mb-2 text-slate-300">Sort</span>
        <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $option?->sort_order ?? 0) }}" class="input input-sm input-bordered bg-slate-950/70 text-white">
    </label>
</div>

<div class="mt-3 flex flex-wrap gap-4">
    <label class="label cursor-pointer justify-start gap-3">
        <input type="checkbox" name="is_default" value="1" class="checkbox checkbox-sm checkbox-warning" @checked(old('is_default', $option?->is_default))>
        <span class="label-text text-slate-300">Default</span>
    </label>

    <label class="label cursor-pointer justify-start gap-3">
        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm checkbox-warning" @checked(old('is_active', $option?->is_active ?? true))>
        <span class="label-text text-slate-300">Active</span>
    </label>
</div>
