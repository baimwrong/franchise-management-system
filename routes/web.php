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
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected application routes
Route::middleware('auth')->group(function () {
    // Dashboard & Context
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/switch-outlet', [DashboardController::class, 'switchOutlet'])->name('dashboard.switch-outlet');

    // Outlets Management (Creation/Deletion strictly Corporate HQ Super Admin; Edit by HQ/Owner/Manager)
    Route::resource('outlets', OutletController::class)->except(['create', 'store', 'destroy', 'edit', 'update']);
    Route::resource('outlets', OutletController::class)->only(['create', 'store', 'destroy'])->middleware('role:super_admin');
    Route::resource('outlets', OutletController::class)->only(['edit', 'update'])->middleware('role:super_admin,franchise_owner,outlet_manager');

    // Products & Central Catalog (Catalog modification strictly Corporate HQ Super Admin)
    Route::resource('products', ProductController::class)->only(['index', 'show']);
    Route::resource('products', ProductController::class)->except(['index', 'show'])->middleware('role:super_admin');
    Route::post('categories', [ProductController::class, 'storeCategory'])->name('categories.store')->middleware('role:super_admin');

    // Multi-Outlet Inventory & Audits
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust')
        ->middleware('role:super_admin,franchise_owner,outlet_manager');
    Route::get('inventory/adjustments-history', [InventoryController::class, 'adjustmentsHistory'])->name('inventory.adjustments');

    // Supply Orders / Requisitions to Brand HQ
    Route::resource('supply-orders', SupplyOrderController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('supply-orders/{supplyOrder}/status', [SupplyOrderController::class, 'updateStatus'])->name('supply-orders.update-status');

    // Sales Transactions & POS
    Route::resource('sales', SalesController::class)->only(['index', 'show', 'create', 'store']);

    // Franchise Royalties & Invoicing (Only Corporate HQ Super Admin and Franchise Licensees)
    Route::get('royalties', [RoyaltyController::class, 'index'])->name('royalties.index')
        ->middleware('role:super_admin,franchise_owner');
    Route::get('royalties/{royaltyInvoice}', [RoyaltyController::class, 'show'])->name('royalties.show')
        ->middleware('role:super_admin,franchise_owner');
    Route::post('royalties/generate', [RoyaltyController::class, 'generate'])->name('royalties.generate')
        ->middleware('role:super_admin');
    Route::patch('royalties/{royaltyInvoice}/paid', [RoyaltyController::class, 'markAsPaid'])->name('royalties.mark-paid')
        ->middleware('role:super_admin');

    // Quality Compliance & Audits (Inspection audits performed by Corporate HQ auditors)
    Route::resource('audits', AuditController::class)->only(['index', 'show']);
    Route::resource('audits', AuditController::class)->only(['create', 'store'])->middleware('role:super_admin');

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
