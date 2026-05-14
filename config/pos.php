<?php

return [
    'roles' => [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'cashier' => 'Cashier',
        'waiter' => 'Waiter',
        'chef' => 'Chef',
        'bartender' => 'Bartender',
    ],

    'permissions' => [
        'dashboard.view' => 'View dashboard',
        'staff.manage' => 'Manage staff users',
        'roles.manage' => 'Manage roles and permissions',
        'branches.manage' => 'Manage branches',
        'tables.manage' => 'Manage dining tables',
        'categories.manage' => 'Manage categories',
        'products.manage' => 'Manage products',
        'operations.manage' => 'Manage table operations and order exceptions',
        'shifts.manage' => 'Manage cashier shifts and reconciliation',
        'refunds.manage' => 'Manage refunds and voids',
        'orders.create' => 'Create orders',
        'orders.view' => 'View orders and receipts',
        'waiter.panel' => 'Take table orders from waiter panel',
        'kitchen.view' => 'View and prepare kitchen orders',
        'bar.view' => 'View and prepare bar orders',
        'reports.view' => 'View reports',
    ],

    'product_stations' => [
        'kitchen' => 'Kitchen',
        'bar' => 'Bar',
    ],

    'table_statuses' => [
        'available' => 'Available',
        'occupied' => 'Occupied',
        'payment_due' => 'Payment Due',
        'reserved' => 'Reserved',
        'cleaning' => 'Cleaning',
        'blocked' => 'Blocked',
    ],

    'preparation_statuses' => [
        'queued' => 'Queued',
        'preparing' => 'Preparing',
        'ready' => 'Ready',
        'served' => 'Served',
    ],

    'service_order_statuses' => [
        'open' => 'Open',
        'in_service' => 'In Service',
        'ready' => 'Ready To Serve',
        'served' => 'Served',
        'paid' => 'Paid',
        'closed' => 'Closed',
        'voided' => 'Voided',
        'merged' => 'Merged',
    ],

    'order_split_statuses' => [
        'draft' => 'Awaiting Payment',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
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

    'waiter_commission_rate' => 0.15,
];
