<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulkImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoyaltyController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SupplyOrderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected application routes
Route::middleware('auth')->group(function () {
    // Dashboard & Context
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/switch-outlet', [DashboardController::class, 'switchOutlet'])->name('dashboard.switch-outlet');

    // Outlets Management
    Route::resource('outlets', OutletController::class);

    // Products & Central Catalog
    Route::resource('products', ProductController::class);
    Route::post('categories', [ProductController::class, 'storeCategory'])->name('categories.store');

    // Multi-Outlet Inventory & Audits
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');
    Route::get('inventory/adjustments-history', [InventoryController::class, 'adjustmentsHistory'])->name('inventory.adjustments');

    // Supply Orders / Requisitions to Brand HQ
    Route::resource('supply-orders', SupplyOrderController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('supply-orders/{supplyOrder}/status', [SupplyOrderController::class, 'updateStatus'])->name('supply-orders.update-status');

    // Sales Transactions & POS
    Route::resource('sales', SalesController::class)->only(['index', 'show', 'create', 'store']);

    // Franchise Royalties & Invoicing
    Route::get('royalties', [RoyaltyController::class, 'index'])->name('royalties.index');
    Route::get('royalties/{royaltyInvoice}', [RoyaltyController::class, 'show'])->name('royalties.show');
    Route::post('royalties/generate', [RoyaltyController::class, 'generate'])->name('royalties.generate')
        ->middleware('role:super_admin');
    Route::patch('royalties/{royaltyInvoice}/paid', [RoyaltyController::class, 'markAsPaid'])->name('royalties.mark-paid')
        ->middleware('role:super_admin,franchise_owner');

    // Quality Compliance & Audits
    Route::resource('audits', AuditController::class)->only(['index', 'show', 'create', 'store']);

    // Users & Staff Management
    Route::resource('users', UserController::class)->middleware('role:super_admin,franchise_owner,outlet_manager');

    // Admin Bulk Import Engine
    Route::prefix('admin/bulk-imports')->name('admin.bulk-imports.')->middleware('role:super_admin')->group(function () {
        Route::get('/', [BulkImportController::class, 'index'])->name('index');
        Route::post('/', [BulkImportController::class, 'store'])->name('store');
        Route::get('/template/{type}', [BulkImportController::class, 'downloadTemplate'])->name('template');
        Route::get('/{bulkImport}', [BulkImportController::class, 'show'])->name('show');
    });
});
