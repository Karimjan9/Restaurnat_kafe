<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\ModifierGroup;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RestaurantPosSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect(config('pos.roles'))
            ->map(fn (string $label, string $name) => Role::updateOrCreate(
                ['name' => $name],
                ['label' => $label],
            ))
            ->keyBy('name');

        $permissions = collect(config('pos.permissions'))
            ->map(fn (string $label, string $name) => Permission::updateOrCreate(
                ['name' => $name],
                ['label' => $label],
            ))
            ->keyBy('name');

        $roles['admin']->permissions()->sync($permissions->pluck('id'));
        $roles['manager']->permissions()->sync($permissions->except(['staff.manage', 'roles.manage'])->pluck('id'));
        $roles['cashier']->permissions()->sync(
            $permissions->only([
                'dashboard.view',
                'orders.create',
                'orders.view',
                'operations.manage',
                'shifts.manage',
                'refunds.manage',
            ])->pluck('id')
        );
        $roles['waiter']->permissions()->sync(
            $permissions->only(['waiter.panel'])->pluck('id')
        );
        $roles['chef']->permissions()->sync(
            $permissions->only(['kitchen.view'])->pluck('id')
        );
        $roles['bartender']->permissions()->sync(
            $permissions->only(['bar.view'])->pluck('id')
        );

        $mainBranch = Branch::updateOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Main Branch',
                'phone' => '+998 90 000 00 01',
                'address' => 'Tashkent city, Main street 12',
                'is_active' => true,
            ],
        );

        $secondBranch = Branch::updateOrCreate(
            ['code' => 'CITY'],
            [
                'name' => 'City Branch',
                'phone' => '+998 90 000 00 02',
                'address' => 'Tashkent city, Chilonzor district',
                'is_active' => true,
            ],
        );

        foreach ([$mainBranch, $secondBranch] as $branch) {
            foreach (range(1, 6) as $number) {
                DiningTable::updateOrCreate(
                    ['branch_id' => $branch->id, 'name' => 'Table '.$number],
                    ['seats' => $number <= 2 ? 2 : 4, 'is_active' => true],
                );
            }
        }

        $categories = collect([
            ['name' => 'Burgers', 'sort_order' => 1],
            ['name' => 'Hot Dishes', 'sort_order' => 2],
            ['name' => 'Drinks', 'sort_order' => 3],
            ['name' => 'Desserts', 'sort_order' => 4],
        ])->map(function (array $category) {
            return Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [...$category, 'is_active' => true],
            );
        })->keyBy('name');

        $products = [
            ['category' => 'Burgers', 'name' => 'Classic Burger', 'sku' => 'BG-001', 'price' => 42000, 'cost_price' => 21000, 'description' => 'Beef patty, cheese, fries', 'station' => 'kitchen'],
            ['category' => 'Burgers', 'name' => 'Chicken Burger', 'sku' => 'BG-002', 'price' => 39000, 'cost_price' => 18000, 'description' => 'Crispy chicken, lettuce, sauce', 'station' => 'kitchen'],
            ['category' => 'Hot Dishes', 'name' => 'Lagman', 'sku' => 'HD-001', 'price' => 36000, 'cost_price' => 14000, 'description' => 'Traditional noodle bowl', 'station' => 'kitchen'],
            ['category' => 'Hot Dishes', 'name' => 'Shashlik Set', 'sku' => 'HD-002', 'price' => 54000, 'cost_price' => 27000, 'description' => 'Three skewers and garnish', 'station' => 'kitchen'],
            ['category' => 'Drinks', 'name' => 'Americano', 'sku' => 'DR-001', 'price' => 18000, 'cost_price' => 5000, 'description' => 'Freshly brewed coffee', 'station' => 'bar'],
            ['category' => 'Drinks', 'name' => 'Lemonade', 'sku' => 'DR-002', 'price' => 16000, 'cost_price' => 6000, 'description' => 'House-made cold lemonade', 'station' => 'bar'],
            ['category' => 'Desserts', 'name' => 'Cheesecake', 'sku' => 'DS-001', 'price' => 22000, 'cost_price' => 9000, 'description' => 'Berry cheesecake slice', 'station' => 'kitchen'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'category_id' => $categories[$product['category']]->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'cost_price' => $product['cost_price'],
                    'station' => $product['station'],
                    'is_active' => true,
                ],
            );
        }

        $modifierGroups = collect([
            [
                'code' => 'burger_extras',
                'name' => 'Burger extras',
                'selection_type' => 'multiple',
                'is_required' => false,
                'min_selected' => 0,
                'max_selected' => 4,
                'sort_order' => 1,
                'options' => [
                    ['code' => 'cheese', 'name' => 'Extra cheese', 'price_delta' => 5000, 'cost_delta' => 2200, 'sort_order' => 1],
                    ['code' => 'jalapeno', 'name' => 'Jalapeno', 'price_delta' => 3000, 'cost_delta' => 900, 'sort_order' => 2],
                    ['code' => 'extra_sauce', 'name' => 'Extra sauce', 'price_delta' => 2000, 'cost_delta' => 700, 'sort_order' => 3],
                ],
            ],
            [
                'code' => 'spice_level',
                'name' => 'Spice level',
                'selection_type' => 'single',
                'is_required' => true,
                'min_selected' => 1,
                'max_selected' => 1,
                'sort_order' => 2,
                'options' => [
                    ['code' => 'mild', 'name' => 'Mild', 'price_delta' => 0, 'cost_delta' => 0, 'is_default' => true, 'sort_order' => 1],
                    ['code' => 'hot', 'name' => 'Hot', 'price_delta' => 0, 'cost_delta' => 0, 'sort_order' => 2],
                    ['code' => 'extra_hot', 'name' => 'Extra hot', 'price_delta' => 0, 'cost_delta' => 0, 'sort_order' => 3],
                ],
            ],
            [
                'code' => 'drink_size',
                'name' => 'Drink size',
                'selection_type' => 'single',
                'is_required' => true,
                'min_selected' => 1,
                'max_selected' => 1,
                'sort_order' => 3,
                'options' => [
                    ['code' => 'regular', 'name' => 'Regular', 'price_delta' => 0, 'cost_delta' => 0, 'is_default' => true, 'sort_order' => 1],
                    ['code' => 'large', 'name' => 'Large', 'price_delta' => 6000, 'cost_delta' => 2400, 'sort_order' => 2],
                ],
            ],
        ])->map(function (array $group) {
            $modifierGroup = ModifierGroup::updateOrCreate(
                ['code' => $group['code']],
                [
                    'name' => $group['name'],
                    'selection_type' => $group['selection_type'],
                    'is_required' => $group['is_required'],
                    'min_selected' => $group['min_selected'],
                    'max_selected' => $group['max_selected'],
                    'sort_order' => $group['sort_order'],
                    'is_active' => true,
                ],
            );

            foreach ($group['options'] as $option) {
                $modifierGroup->options()->updateOrCreate(
                    ['code' => $option['code']],
                    [
                        'name' => $option['name'],
                        'price_delta' => $option['price_delta'],
                        'cost_delta' => $option['cost_delta'],
                        'is_default' => $option['is_default'] ?? false,
                        'is_active' => true,
                        'sort_order' => $option['sort_order'],
                    ],
                );
            }

            return $modifierGroup;
        })->keyBy('code');

        Product::whereIn('sku', ['BG-001', 'BG-002'])->get()->each(function (Product $product) use ($modifierGroups) {
            $product->modifierGroups()->syncWithoutDetaching([
                $modifierGroups['burger_extras']->id => ['sort_order' => 1],
                $modifierGroups['spice_level']->id => ['sort_order' => 2],
            ]);
        });

        Product::whereIn('sku', ['DR-001', 'DR-002'])->get()->each(function (Product $product) use ($modifierGroups) {
            $product->modifierGroups()->syncWithoutDetaching([
                $modifierGroups['drink_size']->id => ['sort_order' => 1],
            ]);
        });

        User::updateOrCreate(
            ['login' => 'admin'],
            [
                'name' => 'System Admin',
                'branch_id' => $mainBranch->id,
                'role_id' => $roles['admin']->id,
                'password' => Hash::make('admin456'),
            ],
        );

        User::updateOrCreate(
            ['login' => 'manager'],
            [
                'name' => 'Floor Manager',
                'branch_id' => $mainBranch->id,
                'role_id' => $roles['manager']->id,
                'password' => Hash::make('manager456'),
            ],
        );

        User::updateOrCreate(
            ['login' => 'cashier'],
            [
                'name' => 'Front Cashier',
                'branch_id' => $mainBranch->id,
                'role_id' => $roles['cashier']->id,
                'password' => Hash::make('cashier456'),
            ],
        );

        User::updateOrCreate(
            ['login' => 'waiter'],
            [
                'name' => 'Main Waiter',
                'branch_id' => $mainBranch->id,
                'role_id' => $roles['waiter']->id,
                'password' => Hash::make('waiter456'),
            ],
        );

        User::updateOrCreate(
            ['login' => 'chef'],
            [
                'name' => 'Head Chef',
                'branch_id' => $mainBranch->id,
                'role_id' => $roles['chef']->id,
                'password' => Hash::make('chef456'),
            ],
        );

        User::updateOrCreate(
            ['login' => 'bartender'],
            [
                'name' => 'Bar Tender',
                'branch_id' => $mainBranch->id,
                'role_id' => $roles['bartender']->id,
                'password' => Hash::make('bartender456'),
            ],
        );
    }
}
