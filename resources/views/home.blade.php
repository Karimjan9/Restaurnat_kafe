@extends('layouts.auth')

@php
    $perPage = 12;
    $initialCategory = $menuCatalog->first();
    $initialItems = collect($initialCategory['items'] ?? [])->values();
    $initialVisibleItems = $initialItems->take($perPage);
    $initialItem = $initialVisibleItems->first();
    $initialPages = max(1, (int) ceil($initialItems->count() / $perPage));

    $formatMoney = static fn (float|int $value): string => number_format((float) $value, 0, '.', ' ') . " so'm";
    $itemInitials = static function (string $name): string {
        $normalized = preg_replace('/[^A-Za-z0-9]/', '', $name) ?: $name;

        return \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($normalized, 0, 2));
    };

    $menuLookup = $menuCatalog->reduce(function ($carry, array $category) {
        foreach ($category['items'] ?? [] as $item) {
            $carry->put((string) $item['id'], $item + [
                'categoryName' => $category['name'],
                'categorySlug' => $category['slug'],
                'theme' => $category['theme'],
                'cue' => $category['cue'],
            ]);
        }

        return $carry;
    }, collect());

    $initialCartSeed = collect();
    if ($initialItems->isNotEmpty()) {
        $initialCartSeed->push(['id' => $initialItems[0]['id'], 'quantity' => 2]);
    }
    if ($initialItems->count() > 1) {
        $initialCartSeed->push(['id' => $initialItems[1]['id'], 'quantity' => 1]);
    }
    if ($initialItems->count() > 2) {
        $initialCartSeed->push(['id' => $initialItems[2]['id'], 'quantity' => 1]);
    }

    $initialCartLines = $initialCartSeed
        ->map(function (array $seed) use ($menuLookup) {
            $item = $menuLookup->get((string) $seed['id']);

            if (! $item) {
                return null;
            }

            $quantity = (int) $seed['quantity'];

            return $item + [
                'quantity' => $quantity,
                'lineTotal' => $quantity * (float) $item['price'],
            ];
        })
        ->filter()
        ->values();

    $initialItemCount = (int) $initialCartLines->sum('quantity');
    $initialSubtotal = (float) $initialCartLines->sum('lineTotal');
@endphp

@section('content')
    <div class="w-full self-start">
        <section id="posPremiumShell" class="pos-premium-shell soft-panel">
            <aside class="pos-premium-rail">
                <div class="pos-premium-rail-brand">CP</div>
                <button type="button" class="pos-premium-rail-button is-active">POS</button>
                <button type="button" class="pos-premium-rail-button">MN</button>
                <button type="button" class="pos-premium-rail-button">RC</button>
                <div class="pos-premium-rail-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </aside>

            <aside class="pos-premium-cart">
                <div class="pos-premium-cart-head">
                    <p class="pos-premium-overline">Order monitor</p>
                    <div class="pos-premium-cart-table">
                        <div>
                            <span>Table 2</span>
                            <strong>Main hall</strong>
                        </div>
                        <strong id="cartOrderNumber" class="pos-premium-cart-ticket">#0016</strong>
                    </div>
                    <p class="pos-premium-cart-copy">
                        Restoran uchun bitta asosiy sahifa endi premium POS ko'rinishida chapda buyurtma, o'ngda esa
                        itemlar vitrinasini bir joyga yig'adi.
                    </p>
                </div>

                <div id="cartList" class="pos-premium-cart-list pos-scroll">
                    @forelse ($initialCartLines as $line)
                        <div class="pos-premium-cart-row" data-cart-row="{{ $line['id'] }}">
                            <div class="min-w-0">
                                <p class="pos-premium-cart-row-title">{{ $line['name'] }}</p>
                                <p class="pos-premium-cart-row-meta">
                                    {{ $line['quantity'] }} x {{ $formatMoney($line['price']) }}
                                </p>
                            </div>

                            <div class="pos-premium-cart-stepper">
                                <button type="button" data-cart-action="decrease" data-item-id="{{ $line['id'] }}">-</button>
                                <span>{{ $line['quantity'] }}</span>
                                <button type="button" data-cart-action="increase" data-item-id="{{ $line['id'] }}">+</button>
                            </div>

                            <strong class="pos-premium-cart-row-total">{{ $formatMoney($line['lineTotal']) }}</strong>
                        </div>
                    @empty
                        <div class="pos-premium-cart-empty">
                            Hozircha cart bo'sh. O'ng tarafdagi kartalardan birini bossangiz buyurtmaga qo'shiladi.
                        </div>
                    @endforelse
                </div>

                <div class="pos-premium-cart-summary">
                    <div class="pos-premium-cart-stat">
                        <span>Items</span>
                        <strong id="cartItemCount">{{ $initialItemCount }}</strong>
                    </div>
                    <div class="pos-premium-cart-stat">
                        <span>Discount</span>
                        <strong id="cartDiscount">0%</strong>
                    </div>
                    <div class="pos-premium-cart-stat is-total">
                        <span>Subtotal</span>
                        <strong id="cartSubtotal">{{ $formatMoney($initialSubtotal) }}</strong>
                    </div>
                </div>

                <button type="button" id="cartCheckout" class="pos-premium-checkout">
                    <span>Checkout</span>
                    <strong id="cartCheckoutTotal">{{ $formatMoney($initialSubtotal) }}</strong>
                </button>
            </aside>

            <div class="pos-premium-main">
                <header class="pos-premium-topbar">
                    <div>
                        <p class="pos-premium-overline">Cafe POS premium</p>
                        <h1 class="pos-premium-title">{{ config('app.name', 'Restaurant POS') }}</h1>
                    </div>

                    <div class="pos-premium-top-actions">
                        <span class="pos-premium-top-pill">{{ $stats['menuItems'] }} items</span>
                        <span class="pos-premium-top-pill">{{ $stats['sections'] }} sections</span>
                        <span class="pos-premium-top-pill">{{ $stats['branches'] }} branches</span>
                        <button type="button" id="newOrderButton" class="pos-premium-top-action is-primary">New order</button>
                        <button type="button" id="receiptButton" class="pos-premium-top-action">Receipt</button>

                        @auth
                            <a href="{{ route('cabinet') }}" class="pos-premium-top-action">Cabinet</a>
                        @else
                            <a href="{{ route('login') }}" class="pos-premium-top-action">Login</a>
                        @endauth
                    </div>
                </header>

                <div class="pos-premium-focus">
                    <div class="pos-premium-focus-copy">
                        <p id="menuCue" class="pos-premium-overline">{{ $initialCategory['cue'] }}</p>
                        <h2 id="menuTitle" class="pos-premium-focus-title">{{ $initialCategory['name'] }}</h2>
                        <p id="menuSummary" class="pos-premium-focus-summary">
                            {{ $initialCategory['summary'] }}
                        </p>
                    </div>

                    <div class="pos-premium-feature">
                        <p class="pos-premium-feature-label">Selected item</p>
                        <p id="menuSelectedCategory" class="pos-premium-feature-category">{{ $initialCategory['name'] }}</p>
                        <h3 id="menuSelectedName" class="pos-premium-feature-name">
                            {{ $initialItem['name'] ?? 'House selection' }}
                        </h3>
                        <p id="menuSelectedDescription" class="pos-premium-feature-copy">
                            {{ $initialItem['description'] ?: "Shu joyda itemning description, narxi va station ma'lumoti yangilanadi." }}
                        </p>
                        <div class="pos-premium-feature-meta">
                            <span id="menuSelectedStation">{{ $initialItem['station'] ?? 'Kitchen' }}</span>
                            <span id="menuSelectedSku">{{ $initialItem['sku'] ?? 'N/A' }}</span>
                            <strong id="menuSelectedPrice">
                                {{ $initialItem ? $formatMoney($initialItem['price']) : 'No price' }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="pos-premium-toolbar">
                    <nav id="menuTabs" class="pos-premium-tabs pos-scroll">
                        @foreach ($menuCatalog as $category)
                            <button
                                type="button"
                                class="pos-premium-tab {{ $category['theme'] }} {{ $category['slug'] === $initialCategory['slug'] ? 'is-active' : '' }}"
                                data-category="{{ $category['slug'] }}"
                                aria-pressed="{{ $category['slug'] === $initialCategory['slug'] ? 'true' : 'false' }}"
                            >
                                <span>{{ $category['name'] }}</span>
                                <strong>{{ $category['count'] }}</strong>
                            </button>
                        @endforeach
                    </nav>

                    <div class="pos-premium-toolbar-actions">
                        <button type="button" id="surprisePick" class="pos-premium-chip">Surprise pick</button>
                        <button type="button" id="chefPick" class="pos-premium-chip">Chef special</button>
                    </div>
                </div>

                <div id="menuGrid" class="pos-premium-grid"></div>

                <footer id="menuPagination" class="pos-premium-pagination {{ $initialPages <= 1 ? 'is-hidden' : '' }}">
                    <p id="menuResultsInfo" class="pos-premium-pagination-copy">
                        Showing {{ $initialVisibleItems->count() }} of {{ $initialItems->count() }} items
                    </p>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="menuPrev" class="pos-premium-page-nav" {{ $initialPages <= 1 ? 'disabled' : '' }}>
                            Prev
                        </button>
                        <div id="menuPager" class="flex flex-wrap items-center gap-2"></div>
                        <button type="button" id="menuNext" class="pos-premium-page-nav" {{ $initialPages <= 1 ? 'disabled' : '' }}>
                            Next
                        </button>
                    </div>
                </footer>
            </div>

            <div id="posPremiumToast" class="pos-premium-toast" aria-live="polite" aria-atomic="true"></div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuCatalog = {{ Illuminate\Support\Js::from($menuCatalog->values()) }};
            const initialCartSeed = {{ Illuminate\Support\Js::from($initialCartSeed->values()) }};

            if (!Array.isArray(menuCatalog) || menuCatalog.length === 0) {
                return;
            }

            const perPage = {{ $perPage }};
            const state = {
                activeCategory: menuCatalog[0].slug,
                selectedItemId: menuCatalog[0].items?.[0]?.id ?? null,
                page: 1,
                orderNumber: 16,
                cart: Object.fromEntries(initialCartSeed.map((line) => [String(line.id), Number(line.quantity || 0)])),
            };

            const itemLookup = Object.fromEntries(
                menuCatalog.flatMap((category) => (category.items ?? []).map((item) => [
                    String(item.id),
                    {
                        ...item,
                        categorySlug: category.slug,
                        categoryName: category.name,
                        theme: category.theme,
                        cue: category.cue,
                        summary: category.summary,
                    },
                ]))
            );

            const refs = {
                cartList: document.getElementById('cartList'),
                cartOrderNumber: document.getElementById('cartOrderNumber'),
                cartItemCount: document.getElementById('cartItemCount'),
                cartSubtotal: document.getElementById('cartSubtotal'),
                cartCheckoutTotal: document.getElementById('cartCheckoutTotal'),
                cartCheckout: document.getElementById('cartCheckout'),
                title: document.getElementById('menuTitle'),
                cue: document.getElementById('menuCue'),
                summary: document.getElementById('menuSummary'),
                selectedCategory: document.getElementById('menuSelectedCategory'),
                selectedName: document.getElementById('menuSelectedName'),
                selectedDescription: document.getElementById('menuSelectedDescription'),
                selectedStation: document.getElementById('menuSelectedStation'),
                selectedSku: document.getElementById('menuSelectedSku'),
                selectedPrice: document.getElementById('menuSelectedPrice'),
                grid: document.getElementById('menuGrid'),
                tabs: Array.from(document.querySelectorAll('[data-category]')),
                pager: document.getElementById('menuPager'),
                pagination: document.getElementById('menuPagination'),
                prev: document.getElementById('menuPrev'),
                next: document.getElementById('menuNext'),
                resultsInfo: document.getElementById('menuResultsInfo'),
                toast: document.getElementById('posPremiumToast'),
            };

            const formatPrice = (value) => `${new Intl.NumberFormat('ru-RU').format(Math.round(Number(value || 0)))} so'm`;
            const escapeHtml = (value) => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
            const getInitials = (value) => {
                const normalized = String(value ?? '').replace(/[^a-z0-9]/gi, '') || String(value ?? 'HP');
                return normalized.slice(0, 2).toUpperCase();
            };

            let toastTimer;

            const getCategory = () => menuCatalog.find((category) => category.slug === state.activeCategory) ?? menuCatalog[0];
            const getItems = (category) => [...(category.items ?? [])];
            const getTotalPages = (category) => Math.max(1, Math.ceil(getItems(category).length / perPage));
            const getPageItems = (category) => {
                const start = (state.page - 1) * perPage;
                return getItems(category).slice(start, start + perPage);
            };
            const getSelectedItem = () => itemLookup[String(state.selectedItemId)] ?? getItems(getCategory())[0] ?? null;
            const getCartEntries = () => Object.entries(state.cart)
                .map(([id, quantity]) => {
                    const item = itemLookup[id];
                    if (!item || quantity <= 0) {
                        return null;
                    }

                    return {
                        ...item,
                        quantity,
                        lineTotal: quantity * Number(item.price || 0),
                    };
                })
                .filter(Boolean);

            const showToast = (message) => {
                if (!refs.toast) {
                    return;
                }

                refs.toast.textContent = message;
                refs.toast.classList.add('is-visible');

                window.clearTimeout(toastTimer);
                toastTimer = window.setTimeout(() => {
                    refs.toast.classList.remove('is-visible');
                }, 2200);
            };

            const addToCart = (itemId, quantity = 1) => {
                const key = String(itemId);
                state.cart[key] = Math.max(0, Number(state.cart[key] || 0) + quantity);

                if (state.cart[key] === 0) {
                    delete state.cart[key];
                }
            };

            const renderTabs = (category) => {
                refs.tabs.forEach((tab) => {
                    const isActive = tab.dataset.category === category.slug;
                    tab.classList.toggle('is-active', isActive);
                    tab.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            };

            const renderHeader = (category, selectedItem) => {
                refs.cue.textContent = category.cue;
                refs.title.textContent = category.name;
                refs.summary.textContent = category.summary;
                refs.selectedCategory.textContent = category.name;

                if (!selectedItem) {
                    refs.selectedName.textContent = 'House selection';
                    refs.selectedDescription.textContent = "Shu joyda itemning description, narxi va station ma'lumoti yangilanadi.";
                    refs.selectedStation.textContent = 'Kitchen';
                    refs.selectedSku.textContent = 'N/A';
                    refs.selectedPrice.textContent = 'No price';
                    return;
                }

                refs.selectedName.textContent = selectedItem.name;
                refs.selectedDescription.textContent = selectedItem.description || "Chef tavsiyasi bilan premium taqdimot.";
                refs.selectedStation.textContent = selectedItem.station || 'Kitchen';
                refs.selectedSku.textContent = selectedItem.sku || 'N/A';
                refs.selectedPrice.textContent = formatPrice(selectedItem.price);
            };

            const renderGrid = (category, pageItems) => {
                if (!pageItems.length) {
                    refs.grid.innerHTML = `
                        <div class="pos-premium-empty">
                            Bu bo'limda hozircha item yo'q.
                        </div>
                    `;
                    return;
                }

                refs.grid.innerHTML = pageItems.map((item) => {
                    const isActive = String(item.id) === String(state.selectedItemId);
                    const quantity = Number(state.cart[String(item.id)] || 0);
                    const safeDescription = item.description || 'Chef tavsiyasi bilan premium taqdimot.';

                    return `
                        <button type="button" class="pos-premium-card ${escapeHtml(category.theme)} ${isActive ? 'is-active' : ''}" data-item-id="${escapeHtml(item.id)}">
                            <span class="pos-premium-card-price">${formatPrice(item.price)}</span>
                            ${quantity > 0 ? `<span class="pos-premium-card-qty">${quantity}</span>` : ''}
                            <div class="pos-premium-card-visual">
                                <span class="pos-premium-card-initials">${escapeHtml(getInitials(item.name))}</span>
                                <span class="pos-premium-card-sheen"></span>
                            </div>
                            <div class="pos-premium-card-body">
                                <p class="pos-premium-card-title">${escapeHtml(item.name)}</p>
                                <p class="pos-premium-card-copy">${escapeHtml(safeDescription)}</p>
                                <div class="pos-premium-card-meta">
                                    <span class="pos-premium-card-station">${escapeHtml(item.station || 'Kitchen')}</span>
                                    <span class="pos-premium-card-add">Tap to add</span>
                                </div>
                            </div>
                        </button>
                    `;
                }).join('');
            };

            const renderPager = (category, pageItems) => {
                const totalPages = getTotalPages(category);
                const start = pageItems.length ? ((state.page - 1) * perPage) + 1 : 0;
                const end = pageItems.length ? start + pageItems.length - 1 : 0;

                refs.pagination.classList.toggle('is-hidden', totalPages <= 1);
                refs.resultsInfo.textContent = pageItems.length
                    ? `Showing ${start}-${end} of ${getItems(category).length} items`
                    : 'No items in this section';

                refs.pager.innerHTML = Array.from({ length: totalPages }, (_, index) => {
                    const page = index + 1;
                    return `
                        <button type="button" class="pos-premium-page ${page === state.page ? 'is-active' : ''}" data-page="${page}">
                            ${page}
                        </button>
                    `;
                }).join('');

                refs.prev.disabled = state.page <= 1;
                refs.next.disabled = state.page >= totalPages;
            };

            const renderCart = () => {
                const cartEntries = getCartEntries();
                const itemCount = cartEntries.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
                const subtotal = cartEntries.reduce((sum, item) => sum + Number(item.lineTotal || 0), 0);

                refs.cartItemCount.textContent = String(itemCount);
                refs.cartSubtotal.textContent = formatPrice(subtotal);
                refs.cartCheckoutTotal.textContent = formatPrice(subtotal);
                refs.cartOrderNumber.textContent = `#${String(state.orderNumber).padStart(4, '0')}`;

                if (!cartEntries.length) {
                    refs.cartList.innerHTML = `
                        <div class="pos-premium-cart-empty">
                            Hozircha cart bo'sh. O'ng tarafdagi kartalardan birini bossangiz buyurtmaga qo'shiladi.
                        </div>
                    `;
                    return;
                }

                refs.cartList.innerHTML = cartEntries.map((item) => `
                    <div class="pos-premium-cart-row" data-cart-row="${escapeHtml(item.id)}">
                        <div class="min-w-0">
                            <p class="pos-premium-cart-row-title">${escapeHtml(item.name)}</p>
                            <p class="pos-premium-cart-row-meta">${item.quantity} x ${formatPrice(item.price)}</p>
                        </div>
                        <div class="pos-premium-cart-stepper">
                            <button type="button" data-cart-action="decrease" data-item-id="${escapeHtml(item.id)}">-</button>
                            <span>${item.quantity}</span>
                            <button type="button" data-cart-action="increase" data-item-id="${escapeHtml(item.id)}">+</button>
                        </div>
                        <strong class="pos-premium-cart-row-total">${formatPrice(item.lineTotal)}</strong>
                    </div>
                `).join('');
            };

            const render = () => {
                const category = getCategory();
                state.page = Math.min(Math.max(1, state.page), getTotalPages(category));

                if (!itemLookup[String(state.selectedItemId)] || itemLookup[String(state.selectedItemId)]?.categorySlug !== category.slug) {
                    state.selectedItemId = getItems(category)[0]?.id ?? null;
                }

                const pageItems = getPageItems(category);

                if (!pageItems.some((item) => String(item.id) === String(state.selectedItemId))) {
                    state.selectedItemId = pageItems[0]?.id ?? getItems(category)[0]?.id ?? null;
                }

                renderTabs(category);
                renderHeader(category, getSelectedItem());
                renderGrid(category, pageItems);
                renderPager(category, pageItems);
                renderCart();
            };

            document.getElementById('menuTabs')?.addEventListener('click', (event) => {
                const button = event.target.closest('[data-category]');
                if (!button) {
                    return;
                }

                const nextCategory = button.dataset.category;
                if (!nextCategory || nextCategory === state.activeCategory) {
                    return;
                }

                state.activeCategory = nextCategory;
                state.page = 1;
                state.selectedItemId = getItems(getCategory())[0]?.id ?? null;
                render();
            });

            refs.grid?.addEventListener('click', (event) => {
                const card = event.target.closest('[data-item-id]');
                if (!card) {
                    return;
                }

                const itemId = card.dataset.itemId;
                const item = itemLookup[String(itemId)];
                if (!item) {
                    return;
                }

                state.selectedItemId = item.id;
                addToCart(item.id, 1);
                render();
                showToast(`${item.name} buyurtmaga qo'shildi`);
            });

            refs.cartList?.addEventListener('click', (event) => {
                const button = event.target.closest('[data-cart-action]');
                if (!button) {
                    return;
                }

                const itemId = button.dataset.itemId;
                const action = button.dataset.cartAction;
                if (!itemId || !action) {
                    return;
                }

                addToCart(itemId, action === 'increase' ? 1 : -1);
                render();
            });

            refs.pager?.addEventListener('click', (event) => {
                const button = event.target.closest('[data-page]');
                if (!button) {
                    return;
                }

                state.page = Number(button.dataset.page || 1);
                const category = getCategory();
                state.selectedItemId = getPageItems(category)[0]?.id ?? getItems(category)[0]?.id ?? null;
                render();
            });

            refs.prev?.addEventListener('click', () => {
                if (refs.prev.disabled) {
                    return;
                }

                state.page -= 1;
                const category = getCategory();
                state.selectedItemId = getPageItems(category)[0]?.id ?? getItems(category)[0]?.id ?? null;
                render();
            });

            refs.next?.addEventListener('click', () => {
                if (refs.next.disabled) {
                    return;
                }

                state.page += 1;
                const category = getCategory();
                state.selectedItemId = getPageItems(category)[0]?.id ?? getItems(category)[0]?.id ?? null;
                render();
            });

            document.getElementById('newOrderButton')?.addEventListener('click', () => {
                state.cart = {};
                state.orderNumber += 1;
                render();
                showToast('Yangi order ochildi');
            });

            document.getElementById('receiptButton')?.addEventListener('click', () => {
                const cartEntries = getCartEntries();
                const subtotal = cartEntries.reduce((sum, item) => sum + Number(item.lineTotal || 0), 0);
                showToast(`Receipt preview: ${cartEntries.length} item, ${formatPrice(subtotal)}`);
            });

            document.getElementById('surprisePick')?.addEventListener('click', () => {
                const category = getCategory();
                const items = getItems(category);
                if (!items.length) {
                    return;
                }

                const item = items[Math.floor(Math.random() * items.length)];
                state.selectedItemId = item.id;
                addToCart(item.id, 1);
                render();
                showToast(`Surprise pick: ${item.name}`);
            });

            document.getElementById('chefPick')?.addEventListener('click', () => {
                const category = getCategory();
                const items = getItems(category);
                if (!items.length) {
                    return;
                }

                const chefItem = items.reduce((best, item) => {
                    if (!best) {
                        return item;
                    }

                    return Number(item.price || 0) > Number(best.price || 0) ? item : best;
                }, null);

                if (!chefItem) {
                    return;
                }

                state.selectedItemId = chefItem.id;
                addToCart(chefItem.id, 1);
                render();
                showToast(`Chef special: ${chefItem.name}`);
            });

            refs.cartCheckout?.addEventListener('click', () => {
                const subtotal = getCartEntries().reduce((sum, item) => sum + Number(item.lineTotal || 0), 0);
                showToast(`Checkout ready: ${formatPrice(subtotal)}`);
            });

            render();
        });
    </script>
@endsection
