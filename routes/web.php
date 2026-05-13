<?php

use Illuminate\Support\Facades\Route;

// ── Auth Routes ──────────────────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'admin.dashboard' : 'login');
});
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Admin Panel Routes (auth required) ─────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Branches
    Route::resource('branches', App\Http\Controllers\Admin\BranchController::class);

    // Categories
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);

    // Brands
    Route::resource('brands', App\Http\Controllers\Admin\BrandController::class);

    // Vehicle Models
    Route::resource('vehicle-models', App\Http\Controllers\Admin\VehicleModelController::class);

    // ── Vehicle Variants (new) ─────────────────────────────────────────
    Route::resource('vehicle-variants', App\Http\Controllers\Admin\VehicleVariantController::class);

    // ── Vehicle Stock / Chassis Registry (new) ─────────────────────────
    Route::resource('vehicle-stock', App\Http\Controllers\Admin\VehicleStockController::class);

    // ── AJAX cascade dropdowns (before resource routes to avoid conflicts) ──
    Route::get('vehicle-variants/by-model/{model}',  [App\Http\Controllers\Admin\VehicleSaleController::class, 'variantsByModel'])->name('vehicle-variants.by-model');
    Route::get('vehicle-stock/by-variant/{variant}', [App\Http\Controllers\Admin\VehicleSaleController::class, 'stockByVariant'])->name('vehicle-stock.by-variant');

    // ── Vehicle Sale Billing Form (new) ────────────────────────────────
    Route::get('vehicle-sales/create', [App\Http\Controllers\Admin\VehicleSaleController::class, 'create'])->name('vehicle-sales.create');
    Route::post('vehicle-sales',       [App\Http\Controllers\Admin\VehicleSaleController::class, 'store'])->name('vehicle-sales.store');

    // Products
    Route::get('products/search', [App\Http\Controllers\Admin\ProductController::class, 'search'])->name('products.search');
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);

    // Inventory
    Route::get('inventory', [App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/{inventory}/adjust', [App\Http\Controllers\Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');

    // Stock Movements (read-only log)
    Route::get('stock-movements', [App\Http\Controllers\Admin\StockMovementController::class, 'index'])->name('stock-movements.index');

    // Customers
    Route::resource('customers', App\Http\Controllers\Admin\CustomerController::class);

    // Suppliers
    Route::resource('suppliers', App\Http\Controllers\Admin\SupplierController::class);

    // Sales (existing – spare parts POS + show/index/edit/delete/PDF)
    Route::get('sales/{sale}/invoice', [App\Http\Controllers\Admin\SaleController::class, 'invoice'])->name('sales.invoice');
    Route::get('sales/export',         [App\Http\Controllers\Admin\SaleController::class, 'export']) ->name('sales.export');
    Route::resource('sales', App\Http\Controllers\Admin\SaleController::class);


    // Purchases
    Route::resource('purchases', App\Http\Controllers\Admin\PurchaseController::class);

    // Expenses
    Route::resource('expenses', App\Http\Controllers\Admin\ExpenseController::class);

    // Payments
    Route::get('payments',  [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments', [App\Http\Controllers\Admin\PaymentController::class, 'store'])->name('payments.store');

    // Reports
    Route::get('reports/profit-loss', [App\Http\Controllers\Admin\ReportController::class, 'profitLoss'])->name('reports.profit-loss');

    // Users
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);

    // ── PDF Inventory Import (new) ─────────────────────────────────────
    Route::resource('pdf-imports', App\Http\Controllers\Admin\PdfImportController::class)
         ->only(['index', 'create', 'store', 'show']);
    Route::post('pdf-imports/{pdfImport}/confirm', [App\Http\Controllers\Admin\PdfImportController::class, 'confirm'])
         ->name('pdf-imports.confirm');

    // ── Price Management & Analytics ───────────────────────────────────
    Route::get('price-management',                  [App\Http\Controllers\Admin\PriceManagementController::class, 'index'])       ->name('price-management.index');
    Route::put('price-management',                  [App\Http\Controllers\Admin\PriceManagementController::class, 'update'])      ->name('price-management.update');
    Route::put('price-management/group',            [App\Http\Controllers\Admin\PriceManagementController::class, 'updateGroup']) ->name('price-management.update-group');
    Route::get('price-management/analytics',        [App\Http\Controllers\Admin\PriceManagementController::class, 'analytics'])   ->name('price-management.analytics');

    // ── Catalogue 2: Unified Setup + Chassis Registry ──────────────────
    Route::get ('catalogue/setup',   [App\Http\Controllers\Admin\CatalogueController::class, 'setup'])        ->name('catalogue.setup');
    Route::post('catalogue/brand',   [App\Http\Controllers\Admin\CatalogueController::class, 'storeBrand'])   ->name('catalogue.brand.store');
    Route::post('catalogue/model',   [App\Http\Controllers\Admin\CatalogueController::class, 'storeModel'])   ->name('catalogue.model.store');
    Route::post('catalogue/variant', [App\Http\Controllers\Admin\CatalogueController::class, 'storeVariant']) ->name('catalogue.variant.store');
    Route::get ('catalogue/chassis', [App\Http\Controllers\Admin\CatalogueController::class, 'chassis'])      ->name('catalogue.chassis');
    Route::post('catalogue/chassis', [App\Http\Controllers\Admin\CatalogueController::class, 'storeChassis']) ->name('catalogue.chassis.store');

    // ── Roles & Permissions Management ─────────────────────────────────
    Route::get ('roles',                        [App\Http\Controllers\Admin\RolePermissionController::class, 'index'])        ->name('roles.index');
    Route::post('roles',                        [App\Http\Controllers\Admin\RolePermissionController::class, 'storeRole'])    ->name('roles.store');
    Route::put ('roles/{role}',                 [App\Http\Controllers\Admin\RolePermissionController::class, 'updateRole'])   ->name('roles.update');
    Route::delete('roles/{role}',               [App\Http\Controllers\Admin\RolePermissionController::class, 'destroyRole'])  ->name('roles.destroy');
    Route::post('permissions',                  [App\Http\Controllers\Admin\RolePermissionController::class, 'storePermission'])->name('permissions.store');
    Route::post('roles/assign-user',            [App\Http\Controllers\Admin\RolePermissionController::class, 'assignRole'])   ->name('roles.assign');

    // ── Vehicle Stock Import ──────────────────────────────────────────
    Route::get ('vehicle-import',           [App\Http\Controllers\Admin\VehicleImportController::class, 'index'])   ->name('vehicle-import.index');
    Route::post('vehicle-import/preview',   [App\Http\Controllers\Admin\VehicleImportController::class, 'preview']) ->name('vehicle-import.preview');
    Route::post('vehicle-import',           [App\Http\Controllers\Admin\VehicleImportController::class, 'store'])   ->name('vehicle-import.store');
    Route::get ('vehicle-import/template',  [App\Http\Controllers\Admin\VehicleImportController::class, 'template'])->name('vehicle-import.template');
});



