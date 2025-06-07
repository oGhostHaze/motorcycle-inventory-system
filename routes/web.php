<?php

use App\Livewire\Dashboard;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Inventory\ProductManagement;
use App\Livewire\Inventory\CategoryManagement;
use App\Livewire\Inventory\WarehouseManagement;
use App\Livewire\Inventory\StockLevels;
use App\Livewire\Inventory\StockMovements;
use App\Livewire\Inventory\LowStockAlerts;
use App\Livewire\Inventory\StockAdjustments;
use App\Livewire\Sales\PointOfSale;
use App\Livewire\Sales\SalesHistory;
use App\Livewire\Sales\CustomerManagement;
use App\Livewire\Purchasing\PurchaseOrderManagement;
use App\Livewire\Purchasing\SupplierManagement;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Dashboard - Accessible to all authenticated users
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Admin Routes - Only for admin users
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', UserManagement::class)->name('users');
    });

    // Inventory Management Routes - For users with manage_inventory permission
    Route::middleware(['permission:manage_inventory'])->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/products', ProductManagement::class)->name('products');
        Route::get('/categories', CategoryManagement::class)->name('categories');
        Route::get('/warehouses', WarehouseManagement::class)->name('warehouses');
        Route::get('/stock-levels', StockLevels::class)->name('stock-levels');
        Route::get('/stock-movements', StockMovements::class)->name('stock-movements');
        Route::get('/low-stock-alerts', LowStockAlerts::class)->name('low-stock-alerts');
        Route::get('/stock-adjustments', StockAdjustments::class)->name('stock-adjustments');
    });

    // Sales Routes - For users with process_sales permission
    Route::middleware(['permission:process_sales'])->prefix('sales')->name('sales.')->group(function () {
        Route::get('/pos', PointOfSale::class)->name('pos');
        Route::get('/history', SalesHistory::class)->name('history');
        Route::get('/customers', CustomerManagement::class)->name('customers');
    });

    // Purchasing Routes - For users with manage_inventory permission
    Route::middleware(['permission:manage_inventory'])->prefix('purchasing')->name('purchasing.')->group(function () {
        Route::get('/purchase-orders', PurchaseOrderManagement::class)->name('purchase-orders');
        Route::get('/suppliers', SupplierManagement::class)->name('suppliers');
    });

    // Quick Access Routes - Based on permissions
    Route::middleware(['permission:process_sales'])->group(function () {
        Route::get('/quick/new-sale', function () {
            return redirect()->route('sales.pos');
        })->name('quick.new-sale');
    });

    Route::middleware(['permission:manage_inventory'])->group(function () {
        Route::get('/quick/add-product', function () {
            return redirect()->route('inventory.products');
        })->name('quick.add-product');

        Route::get('/quick/stock-adjustment', function () {
            return redirect()->route('inventory.stock-adjustments');
        })->name('quick.stock-adjustment');

        Route::get('/quick/new-purchase-order', function () {
            return redirect()->route('purchasing.purchase-orders');
        })->name('quick.new-purchase-order');
    });

    // Future Routes - Placeholder for upcoming features
    Route::middleware(['permission:view_reports'])->prefix('reports')->name('reports.')->group(function () {
        // These will be implemented in future phases
        Route::get('/sales', function () {
            return view('placeholder', ['title' => 'Sales Reports', 'message' => 'Coming Soon']);
        })->name('sales');

        Route::get('/inventory', function () {
            return view('placeholder', ['title' => 'Inventory Reports', 'message' => 'Coming Soon']);
        })->name('inventory');

        Route::get('/financial', function () {
            return view('placeholder', ['title' => 'Financial Reports', 'message' => 'Coming Soon']);
        })->name('financial');

        Route::get('/customers', function () {
            return view('placeholder', ['title' => 'Customer Reports', 'message' => 'Coming Soon']);
        })->name('customers');
    });

    // Additional Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/settings', function () {
            return view('placeholder', ['title' => 'System Settings', 'message' => 'Coming Soon']);
        })->name('settings');

        Route::get('/activity-logs', function () {
            return view('placeholder', ['title' => 'Activity Logs', 'message' => 'Coming Soon']);
        })->name('activity-logs');

        Route::get('/backup', function () {
            return view('placeholder', ['title' => 'Database Backup', 'message' => 'Coming Soon']);
        })->name('backup');
    });

    // Add this to your routes/web.php temporarily for debugging

    Route::middleware(['auth'])->get('/debug-permissions', function () {
        $user = auth()->user();

        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role ?? 'No role set',
            'user_permissions' => $user->permissions ?? 'No permissions set',
            'is_admin' => method_exists($user, 'isAdmin') ? $user->isAdmin() : 'Method not found',
            'can_manage_inventory' => $user->can('manage_inventory'),
            'can_process_sales' => $user->can('process_sales'),
            'can_view_reports' => $user->can('view_reports'),
            'has_manage_inventory_permission' => method_exists($user, 'hasPermission') ? $user->hasPermission('manage_inventory') : 'Method not found',
            'can_manage_inventory_method' => method_exists($user, 'canManageInventory') ? $user->canManageInventory() : 'Method not found',
            'default_admin_permissions' => $user::getDefaultPermissions('admin'),
        ];
    });
});
