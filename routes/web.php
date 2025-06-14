<?php

use App\Livewire\Dashboard;
use App\Livewire\Sales\PointOfSale;
use App\Livewire\Sales\SalesHistory;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Inventory\StockLevels;
use App\Livewire\Sales\ShiftManagement;
use App\Livewire\Sales\ReturnsManagement;
use App\Livewire\Inventory\LowStockAlerts;
use App\Livewire\Inventory\StockMovements;
use App\Livewire\Sales\CustomerManagement;
use App\Livewire\Admin\RecomputeManagement;
use App\Livewire\Inventory\StockAdjustments;
use App\Livewire\Inventory\ProductManagement;
use App\Livewire\Inventory\CategoryManagement;
use App\Livewire\Inventory\WarehouseManagement;
use App\Livewire\Purchasing\SupplierManagement;
use App\Livewire\Purchasing\PurchaseOrderManagement;
use App\Http\Controllers\InvoiceController; // Add this import

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

        Route::get('/recompute', RecomputeManagement::class)->name('recompute');

        Route::post('/recompute/run', function () {
            $type = request('type', 'all');
            $dryRun = request('dry_run', false);

            $command = 'returns:recompute';

            switch ($type) {
                case 'shifts':
                    $command .= ' --shifts';
                    break;
                case 'items':
                    $command .= ' --items';
                    break;
                default:
                    $command .= ' --all';
            }

            if ($dryRun) {
                $command .= ' --dry-run';
            }

            // Capture command output
            $output = [];
            $exitCode = 0;
            exec("cd " . base_path() . " && php artisan {$command} 2>&1", $output, $exitCode);

            return response()->json([
                'success' => $exitCode === 0,
                'output' => implode("\n", $output),
                'command' => $command
            ]);
        })->name('recompute.run');
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
        Route::get('/shifts', ShiftManagement::class)->name('shifts');
        Route::get('/returns', ReturnsManagement::class)->name('returns');
    });

    // Invoice Routes - Fixed implementation
    Route::middleware(['permission:process_sales'])->prefix('invoice')->name('invoice.')->group(function () {
        Route::get('/{sale}/download', [InvoiceController::class, 'download'])->name('download');
        Route::get('/{sale}/preview', [InvoiceController::class, 'preview'])->name('preview');
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
});
