<?php

return [
    'roles' => [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'cashier' => 'Cashier',
    ],

    'permissions' => [
        'dashboard.view' => 'View dashboard',
        'staff.manage' => 'Manage staff users',
        'roles.manage' => 'Manage roles and permissions',
        'branches.manage' => 'Manage branches',
        'tables.manage' => 'Manage dining tables',
        'categories.manage' => 'Manage categories',
        'products.manage' => 'Manage products',
        'orders.create' => 'Create orders',
        'orders.view' => 'View orders and receipts',
        'reports.view' => 'View reports',
    ],

    'order_types' => [
        'dine_in' => 'Dine-in',
        'takeaway' => 'Takeaway',
        'delivery' => 'Delivery',
    ],

    'payment_methods' => [
        'cash' => 'Cash',
        'card' => 'Card',
        'transfer' => 'Bank transfer',
    ],
];
