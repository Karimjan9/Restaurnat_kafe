<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PosDashboard extends Component
{
    public string $search = '';

    public string $categoryId = 'all';

    public string $orderType = 'dine_in';

    public ?int $branchId = null;

    public ?int $tableId = null;

    public string $customerName = '';

    public string $customerPhone = '';

    public string $deliveryAddress = '';

    public string $paymentMethod = 'cash';

    public string $notes = '';

    /** @var array<int, int> */
    public array $cart = [];

    public function mount(): void
    {
        $this->branchId = auth()->user()->branch_id
            ?? Branch::where('is_active', true)->value('id');

        $this->tableId = $this->availableTables()->first()?->id;
    }

    public function setCategory(string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function addProduct(int $productId): void
    {
        $this->cart[$productId] = ($this->cart[$productId] ?? 0) + 1;
    }

    public function incrementQuantity(int $productId): void
    {
        $this->addProduct($productId);
    }

    public function decrementQuantity(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $this->cart[$productId]--;

        if ($this->cart[$productId] <= 0) {
            unset($this->cart[$productId]);
        }
    }

    public function removeProduct(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function updatedBranchId(): void
    {
        if ($this->orderType !== 'dine_in') {
            return;
        }

        $this->tableId = $this->availableTables()->first()?->id;
    }

    public function updatedOrderType(string $value): void
    {
        if ($value !== 'dine_in') {
            $this->tableId = null;
        } else {
            $this->tableId = $this->availableTables()->first()?->id;
        }

        if ($value !== 'delivery') {
            $this->deliveryAddress = '';
        }

        if ($value === 'dine_in') {
            $this->customerName = '';
            $this->customerPhone = '';
        }
    }

    public function checkout()
    {
        if ($this->cartItems()->isEmpty()) {
            $this->addError('cart', 'Kamida bitta mahsulot qo‘shing.');

            return null;
        }

        $validated = $this->validate([
            'branchId' => ['required', 'exists:branches,id'],
            'orderType' => ['required', Rule::in(array_keys(config('pos.order_types')))],
            'tableId' => [Rule::requiredIf($this->orderType === 'dine_in'), 'nullable', 'exists:dining_tables,id'],
            'customerName' => [Rule::requiredIf(in_array($this->orderType, ['takeaway', 'delivery'], true)), 'nullable', 'string', 'max:255'],
            'customerPhone' => [Rule::requiredIf(in_array($this->orderType, ['takeaway', 'delivery'], true)), 'nullable', 'string', 'max:255'],
            'deliveryAddress' => [Rule::requiredIf($this->orderType === 'delivery'), 'nullable', 'string'],
            'paymentMethod' => ['required', Rule::in(array_keys(config('pos.payment_methods')))],
            'notes' => ['nullable', 'string'],
        ]);

        if ($this->orderType === 'dine_in' && ! $this->availableTables()->contains('id', $this->tableId)) {
            $this->addError('tableId', 'Tanlangan stol ushbu filialga tegishli emas.');

            return null;
        }

        $cartItems = $this->cartItems();
        $subtotal = $cartItems->sum('line_total');

        $order = DB::transaction(function () use ($validated, $cartItems, $subtotal) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'branch_id' => $validated['branchId'],
                'dining_table_id' => $validated['tableId'],
                'user_id' => auth()->id(),
                'order_type' => $validated['orderType'],
                'status' => 'paid',
                'customer_name' => $validated['customerName'] ?: null,
                'customer_phone' => $validated['customerPhone'] ?: null,
                'delivery_address' => $validated['deliveryAddress'] ?: null,
                'notes' => $validated['notes'] ?: null,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'placed_at' => now(),
                'paid_at' => now(),
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'line_total' => $item['line_total'],
                ]);
            }

            $order->payments()->create([
                'user_id' => auth()->id(),
                'method' => $validated['paymentMethod'],
                'amount' => $subtotal,
                'paid_at' => now(),
            ]);

            return $order;
        });

        $this->resetCheckoutState();
        session()->flash('status', 'Order muvaffaqiyatli yaratildi.');

        return redirect()->route('orders.receipt', $order);
    }

    protected function availableTables(): Collection
    {
        if (! $this->branchId) {
            return collect();
        }

        return DiningTable::query()
            ->where('branch_id', $this->branchId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    protected function cartItems(): Collection
    {
        $products = Product::query()
            ->with('category')
            ->whereIn('id', array_keys($this->cart))
            ->get()
            ->keyBy('id');

        return collect($this->cart)
            ->map(function (int $quantity, int|string $productId) use ($products) {
                $product = $products->get((int) $productId);

                if (! $product) {
                    return null;
                }

                $lineTotal = $quantity * (float) $product->price;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'price' => (float) $product->price,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ];
            })
            ->filter()
            ->values();
    }

    protected function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd-His').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    protected function resetCheckoutState(): void
    {
        $this->search = '';
        $this->categoryId = 'all';
        $this->orderType = 'dine_in';
        $this->tableId = $this->availableTables()->first()?->id;
        $this->customerName = '';
        $this->customerPhone = '';
        $this->deliveryAddress = '';
        $this->paymentMethod = 'cash';
        $this->notes = '';
        $this->cart = [];
        $this->resetErrorBag();
    }

    public function render()
    {
        $search = trim($this->search);
        $cartItems = $this->cartItems();

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($this->categoryId !== 'all', fn ($query) => $query->where('category_id', (int) $this->categoryId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->get();

        return view('livewire.pos-dashboard', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'availableTables' => $this->availableTables(),
            'products' => $products,
            'cartItems' => $cartItems,
            'subtotal' => $cartItems->sum('line_total'),
            'recentOrders' => Order::query()
                ->with(['cashier', 'branch'])
                ->when($this->branchId, fn ($query) => $query->where('branch_id', $this->branchId))
                ->latest('paid_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
