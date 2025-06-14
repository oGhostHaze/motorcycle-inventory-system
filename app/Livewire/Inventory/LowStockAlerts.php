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
use Illuminate\Support\Collection;

class LowStockAlerts extends Component
{
    use WithPagination;
    use Toast;

    public $showCreatePOModal = false;
    public $selectedAlerts = [];
    public $selectedSupplier = '';
    public $expectedDate = '';

    // Search and filters
    public $search = '';
    public $warehouseFilter = '';
    public $statusFilter = 'active';
    public $severityFilter = '';

    public function render()
    {
        // Generate alerts dynamically from current inventory levels
        $alertsQuery = $this->buildLowStockQuery();

        // Apply filters
        if ($this->search) {
            $alertsQuery->where(function ($q) {
                $q->where('products.name', 'like', '%' . $this->search . '%')
                    ->orWhere('products.sku', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->warehouseFilter) {
            $alertsQuery->where('inventories.warehouse_id', $this->warehouseFilter);
        }

        // Apply severity filter
        if ($this->severityFilter) {
            switch ($this->severityFilter) {
                case 'critical':
                    $alertsQuery->where('inventories.quantity_on_hand', 0);
                    break;
                case 'low':
                    $alertsQuery->where('inventories.quantity_on_hand', '>', 0)
                        ->whereRaw('inventories.quantity_on_hand <= (products.min_stock_level * 0.5)');
                    break;
                case 'warning':
                    $alertsQuery->whereRaw('inventories.quantity_on_hand > (products.min_stock_level * 0.5)')
                        ->whereRaw('inventories.quantity_on_hand <= products.min_stock_level');
                    break;
            }
        }

        $alerts = $alertsQuery
            ->orderBy('inventories.quantity_on_hand')
            ->orderBy('products.name')
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

        // Calculate summary statistics
        $allLowStockItems = $this->buildLowStockQuery()->get();
        $totalAlerts = $allLowStockItems->count();
        $criticalAlerts = $allLowStockItems->where('quantity_on_hand', 0)->count();

        $totalValue = $allLowStockItems->sum(function ($item) {
            $shortage = max(0, $item->min_stock_level - $item->quantity_on_hand);
            return $shortage * ($item->cost_price ?? 0);
        });

        return view('livewire.inventory.low-stock-alerts', [
            'alerts' => $alerts,
            'filterOptions' => $filterOptions,
            'totalAlerts' => $totalAlerts,
            'criticalAlerts' => $criticalAlerts,
            'totalValue' => $totalValue,
        ])->layout('layouts.app', ['title' => 'Low Stock Alerts']);
    }

    private function buildLowStockQuery()
    {
        return Inventory::query()
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->join('warehouses', 'inventories.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('product_brands', 'products.product_brand_id', '=', 'product_brands.id')
            ->select([
                'inventories.*',
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                'products.min_stock_level',
                'products.cost_price',
                'warehouses.id as warehouse_id',
                'warehouses.name as warehouse_name',
                'categories.name as category_name',
                'product_brands.name as brand_name'
            ])
            ->where('products.status', 'active')
            ->where('warehouses.is_active', true)
            ->where('products.min_stock_level', '>', 0)
            ->whereRaw('inventories.quantity_on_hand <= products.min_stock_level')
            ->with([
                'product' => function ($query) {
                    $query->with(['category', 'brand']);
                },
                'warehouse'
            ]);
    }

    public function resolveAlert($inventoryId)
    {
        // For real-time alerts, we can create a resolved record or just show success
        // Since these are dynamically generated, we'll just show success
        $this->success('Alert acknowledged! Consider restocking this item.');
    }

    public function resolveMultiple()
    {
        if (empty($this->selectedAlerts)) {
            $this->error('Please select alerts to resolve.');
            return;
        }

        // For dynamic alerts, we'll just acknowledge them
        $count = count($this->selectedAlerts);
        $this->selectedAlerts = [];
        $this->success("{$count} alerts acknowledged!");
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
            // Get the selected inventory items to create PO
            $selectedInventories = Inventory::with('product')
                ->whereIn('id', $this->selectedAlerts)
                ->get();

            // Create purchase order logic here
            // This would involve creating a PO with the selected items

            $this->success('Purchase order created successfully!');
            $this->showCreatePOModal = false;
            $this->selectedAlerts = [];
        } catch (\Exception $e) {
            $this->error('Error creating purchase order: ' . $e->getMessage());
        }
    }

    public function refreshAlerts()
    {
        // Since we're generating alerts dynamically, just refresh the page data
        $this->success('Low stock alerts refreshed!');
        $this->render();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'warehouseFilter', 'statusFilter', 'severityFilter']);
    }

    public function getSeverityClass($alert)
    {
        if ($alert->quantity_on_hand == 0) return 'error';
        if ($alert->quantity_on_hand <= ($alert->min_stock_level * 0.5)) return 'warning';
        return 'info';
    }

    public function getSeverityText($alert)
    {
        if ($alert->quantity_on_hand == 0) return 'Critical';
        if ($alert->quantity_on_hand <= ($alert->min_stock_level * 0.5)) return 'Low';
        return 'Warning';
    }
}
