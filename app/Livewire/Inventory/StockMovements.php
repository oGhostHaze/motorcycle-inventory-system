<?php

namespace App\Livewire\Inventory;

use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class StockMovements extends Component
{
    use WithPagination;
    use Toast;

    // Search and filters
    public string $search = '';
    public string $productFilter = '';
    public string $warehouseFilter = '';
    public string $userFilter = '';
    public string $typeFilter = '';
    public string $dateFilter = '';

    public function render()
    {
        $movements = StockMovement::with(['product', 'warehouse', 'user', 'reference'])
            ->when($this->search, fn($q) => $q->whereHas('product', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            }))
            ->when($this->productFilter, fn($q) => $q->where('product_id', $this->productFilter))
            ->when($this->warehouseFilter, fn($q) => $q->where('warehouse_id', $this->warehouseFilter))
            ->when($this->userFilter, fn($q) => $q->where('user_id', $this->userFilter))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->dateFilter, function ($q) {
                switch ($this->dateFilter) {
                    case 'today':
                        return $q->whereDate('created_at', today());
                    case 'week':
                        return $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    case 'month':
                        return $q->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year);
                    case 'year':
                        return $q->whereYear('created_at', now()->year);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        $filterOptions = [
            'products' => $products->map(fn($p) => ['value' => $p->id, 'label' => $p->name]),
            'warehouses' => $warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name]),
            'users' => $users->map(fn($u) => ['value' => $u->id, 'label' => $u->name]),
            'types' => [
                ['value' => '', 'label' => 'All Types'],
                ['value' => 'sale', 'label' => 'Sale'],
                ['value' => 'purchase', 'label' => 'Purchase'],
                ['value' => 'adjustment', 'label' => 'Adjustment'],
                ['value' => 'transfer_out', 'label' => 'Transfer Out'],
                ['value' => 'transfer_in', 'label' => 'Transfer In'],
                ['value' => 'return', 'label' => 'Return'],
            ],
            'dates' => [
                ['value' => '', 'label' => 'All Time'],
                ['value' => 'today', 'label' => 'Today'],
                ['value' => 'week', 'label' => 'This Week'],
                ['value' => 'month', 'label' => 'This Month'],
                ['value' => 'year', 'label' => 'This Year'],
            ]
        ];

        return view('livewire.inventory.stock-movements', [
            'movements' => $movements,
            'filterOptions' => $filterOptions,
        ])->layout('layouts.app', ['title' => 'Stock Movements']);
    }

    public function clearFilters()
    {
        $this->reset(['search', 'productFilter', 'warehouseFilter', 'userFilter', 'typeFilter', 'dateFilter']);
    }

    public function getMovementTypeClass($type)
    {
        return match ($type) {
            'sale' => 'error',
            'purchase', 'transfer_in', 'return' => 'success',
            'adjustment' => 'warning',
            'transfer_out' => 'info',
            default => 'neutral',
        };
    }

    public function getMovementIcon($type)
    {
        return match ($type) {
            'sale' => 'o-shopping-cart',
            'purchase' => 'o-truck',
            'adjustment' => 'o-adjustments-horizontal',
            'transfer_out' => 'o-arrow-right',
            'transfer_in' => 'o-arrow-left',
            'return' => 'o-arrow-uturn-left',
            default => 'o-cube',
        };
    }
}
