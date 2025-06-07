<?php

namespace App\Livewire\Inventory;

use App\Models\LowStockAlert;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class LowStockAlerts extends Component
{
    use WithPagination;
    use Toast;

    public bool $showCreatePOModal = false;
    public array $selectedAlerts = [];
    public $selectedSupplier = '';
    public $expectedDate = '';

    // Search and filters
    public string $search = '';
    public string $warehouseFilter = '';
    public string $statusFilter = 'active';
    public string $severityFilter = '';

    public function render()
    {
        $alerts = LowStockAlert::with(['product.category', 'product.brand', 'warehouse'])
            ->when($this->search, fn($q) => $q->whereHas('product', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            }))
            ->when($this->warehouseFilter, fn($q) => $q->where('warehouse_id', $this->warehouseFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->severityFilter, function ($q) {
                switch ($this->severityFilter) {
                    case 'critical':
                        return $q->where('current_stock', 0);
                    case 'low':
                        return $q->where('current_stock', '>', 0)
                            ->whereRaw('current_stock <= (products.min_stock_level * 0.5)');
                    case 'warning':
                        return $q->whereRaw('current_stock > (products.min_stock_level * 0.5)')
                            ->whereRaw('current_stock <= products.min_stock_level');
                }
            })
            ->orderBy('current_stock')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $filterOptions = [
            'warehouses' => $warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name]),
            'statuses' => [
                ['value' => '', 'label' => 'All Alerts'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'resolved', 'label' => 'Resolved'],
            ],
            'severities' => [
                ['value' => '', 'label' => 'All Levels'],
                ['value' => 'critical', 'label' => 'Critical (Out of Stock)'],
                ['value' => 'low', 'label' => 'Low Stock'],
                ['value' => 'warning', 'label' => 'Warning Level'],
            ]
        ];

        // Summary statistics
        $totalAlerts = LowStockAlert::where('status', 'active')->count();
        $criticalAlerts = LowStockAlert::where('status', 'active')
            ->where('current_stock', 0)->count();
        $totalValue = LowStockAlert::join('products', 'low_stock_alerts.product_id', '=', 'products.id')
            ->where('low_stock_alerts.status', 'active')
            ->selectRaw('SUM((products.min_stock_level - current_stock) * cost_price) as total')
            ->value('total') ?? 0;

        return view('livewire.inventory.low-stock-alerts', [
            'alerts' => $alerts,
            'filterOptions' => $filterOptions,
            'totalAlerts' => $totalAlerts,
            'criticalAlerts' => $criticalAlerts,
            'totalValue' => $totalValue,
        ])->layout('layouts.app', ['title' => 'Low Stock Alerts']);
    }

    public function resolveAlert($alertId)
    {
        $alert = LowStockAlert::find($alertId);
        if ($alert) {
            $alert->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);
            $this->success('Alert resolved successfully!');
        }
    }

    public function resolveMultiple()
    {
        if (empty($this->selectedAlerts)) {
            $this->error('Please select alerts to resolve.');
            return;
        }

        LowStockAlert::whereIn('id', $this->selectedAlerts)->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $count = count($this->selectedAlerts);
        $this->selectedAlerts = [];
        $this->success("{$count} alerts resolved successfully!");
    }

    public function openCreatePOModal()
    {
        if (empty($this->selectedAlerts)) {
            $this->error('Please select alerts to create purchase order.');
            return;
        }

        $this->expectedDate = now()->addDays(7)->format('Y-m-d');
        $this->showCreatePOModal = true;
    }

    public function createPurchaseOrder()
    {
        $this->validate([
            'selectedSupplier' => 'required|exists:suppliers,id',
            'expectedDate' => 'required|date|after:today',
        ]);

        try {
            // Create purchase order logic here
            $this->success('Purchase order created successfully!');
            $this->showCreatePOModal = false;
            $this->selectedAlerts = [];
        } catch (\Exception $e) {
            $this->error('Error creating purchase order: ' . $e->getMessage());
        }
    }

    public function refreshAlerts()
    {
        // Logic to refresh/regenerate alerts
        $products = Product::with('inventory')->get();

        foreach ($products as $product) {
            foreach ($product->inventory as $inventory) {
                if ($inventory->quantity_on_hand <= $product->min_stock_level) {
                    LowStockAlert::firstOrCreate([
                        'product_id' => $product->id,
                        'warehouse_id' => $inventory->warehouse_id,
                        'status' => 'active',
                    ], [
                        'current_stock' => $inventory->quantity_on_hand,
                        'min_stock_level' => $product->min_stock_level,
                    ]);
                }
            }
        }

        $this->success('Low stock alerts refreshed!');
    }

    public function clearFilters()
    {
        $this->reset(['search', 'warehouseFilter', 'statusFilter', 'severityFilter']);
    }

    public function getSeverityClass($alert)
    {
        if ($alert->current_stock == 0) return 'error';
        if ($alert->current_stock <= ($alert->min_stock_level * 0.5)) return 'warning';
        return 'info';
    }

    public function getSeverityText($alert)
    {
        if ($alert->current_stock == 0) return 'Critical';
        if ($alert->current_stock <= ($alert->min_stock_level * 0.5)) return 'Low';
        return 'Warning';
    }
}
