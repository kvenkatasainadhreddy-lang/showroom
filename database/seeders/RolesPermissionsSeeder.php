<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Branches
            'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
            // Categories
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            // Brands & Models
            'brands.view', 'brands.create', 'brands.edit', 'brands.delete',
            // Products
            'products.view', 'products.create', 'products.edit', 'products.delete',
            // Inventory
            'inventory.view', 'inventory.adjust',
            // Stock Movements
            'stock_movements.view',
            // Customers
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            // Suppliers
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            // Sales
            'sales.view', 'sales.create', 'sales.edit', 'sales.delete',
            // Purchases
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete',
            // Expenses
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            // Payments
            'payments.view', 'payments.create',
            // Reports
            'reports.view',
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── Admin: all permissions ───────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        // ── Salesperson ──────────────────────────────────────────────
        $salesperson = Role::firstOrCreate(['name' => 'salesperson', 'guard_name' => 'web']);
        $salesperson->syncPermissions([
            'products.view',
            'inventory.view',
            'stock_movements.view',
            'customers.view', 'customers.create', 'customers.edit',
            'sales.view', 'sales.create',
            'payments.view', 'payments.create',
        ]);

        // ── Accountant ───────────────────────────────────────────────
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'sales.view',
            'purchases.view',
            'expenses.view', 'expenses.create', 'expenses.edit',
            'payments.view',
            'reports.view',
            'stock_movements.view',
        ]);
    }
}
