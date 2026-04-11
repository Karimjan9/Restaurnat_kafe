<section class="flex min-h-[32rem] flex-col bg-slate-50/70">
    <header class="shrink-0 border-b border-slate-200/80 bg-white/85 px-4 py-5 backdrop-blur lg:px-5">
        <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
                <p class="text-[11px] uppercase tracking-[0.34em] text-violet-500">Tablet menu</p>
                <h3 class="mt-2 text-2xl font-semibold text-slate-900">Visual order browser</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Category filter, quick search va settlement shortcutlar bitta top bar ichida.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="startNewOrder" class="pos-top-action is-primary">+ New order</button>
                <a href="#pos-settlements" class="pos-top-action">Settlements</a>
                <a href="#pos-receipts" class="pos-top-action">Receipts</a>
                <span class="pos-top-action">
                    <span class="pos-live-dot mr-2 inline-block h-2.5 w-2.5 rounded-full bg-emerald-400 align-middle"></span>
                    Live sync
                </span>
            </div>
        </div>

        <div class="mt-5 grid gap-3 xl:grid-cols-[minmax(0,1fr)_14rem_11rem]">
            <label class="block">
                <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Search product</span>
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full rounded-[1.3rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100" placeholder="Burger, coffee, sku...">
            </label>

            <label class="block">
                <span class="mb-2 block text-xs uppercase tracking-[0.24em] text-slate-500">Branch</span>
                <select wire:model.live="branchId" class="w-full rounded-[1.3rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>

            <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Ready to settle</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $serviceOrders->count() }}</p>
                <p class="mt-1 text-xs text-slate-500">Waiter orders in flow</p>
            </div>
        </div>
    </header>

    <div class="pos-scroll min-h-0 flex-1 px-4 py-5 lg:px-5">
        @include('livewire.pos.menu-grid')

        <div class="mt-5 grid gap-5 2xl:grid-cols-[minmax(0,1.04fr)_minmax(23rem,0.96fr)]">
            @include('livewire.pos.settlements')
        </div>

        @include('livewire.pos.receipts')
    </div>
</section>
