<?php

namespace App\Livewire\Inventory;

use App\Models\Warehouse;
use App\Models\Inventory;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class WarehouseManagement extends Component
{
    use WithPagination;
    use Toast;

    public bool $showModal = false;
    public bool $editMode = false;
    public ?Warehouse $selectedWarehouse = null;

    // Form fields
    public string $name = '';
    public string $code = '';
    public string $address = '';
    public string $city = '';
    public string $manager_name = '';
    public string $phone = '';
    public string $type = 'main';
    public bool $is_active = true;

    // Search and filters
    public string $search = '';
    public string $typeFilter = '';
    public string $statusFilter = '';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:10|unique:warehouses,code',
        'address' => 'required|string|max:500',
        'city' => 'required|string|max:100',
        'manager_name' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'type' => 'required|in:main,retail,storage,overflow',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $warehouses = Warehouse::withCount(['inventory', 'sales', 'purchaseOrders'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%')
                ->orWhere('city', 'like', '%' . $this->search . '%'))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $typeOptions = [
            ['value' => '', 'label' => 'All Types'],
            ['value' => 'main', 'label' => 'Main Warehouse'],
            ['value' => 'retail', 'label' => 'Retail Store'],
            ['value' => 'storage', 'label' => 'Storage Facility'],
            ['value' => 'overflow', 'label' => 'Overflow Storage'],
        ];

        $statusOptions = [
            ['value' => '', 'label' => 'All Status'],
            ['value' => '1', 'label' => 'Active'],
            ['value' => '0', 'label' => 'Inactive'],
        ];

        return view('livewire.inventory.warehouse-management', [
            'warehouses' => $warehouses,
            'typeOptions' => $typeOptions,
            'statusOptions' => $statusOptions,
        ])->layout('layouts.app', ['title' => 'Warehouse Management']);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->selectedWarehouse = null;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function editWarehouse(Warehouse $warehouse)
    {
        $this->selectedWarehouse = $warehouse;
        $this->name = $warehouse->name;
        $this->code = $warehouse->code;
        $this->address = $warehouse->address;
        $this->city = $warehouse->city;
        $this->manager_name = $warehouse->manager_name ?? '';
        $this->phone = $warehouse->phone ?? '';
        $this->type = $warehouse->type;
        $this->is_active = $warehouse->is_active;
        $this->editMode = true;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function save()
    {
        if ($this->editMode) {
            $this->rules['code'] = 'required|string|max:10|unique:warehouses,code,' . $this->selectedWarehouse->id;
        }

        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'address' => $this->address,
                'city' => $this->city,
                'manager_name' => $this->manager_name,
                'phone' => $this->phone,
                'type' => $this->type,
                'is_active' => $this->is_active,
            ];

            if ($this->editMode) {
                $this->selectedWarehouse->update($data);
                $this->success('Warehouse updated successfully!');
            } else {
                Warehouse::create($data);
                $this->success('Warehouse created successfully!');
            }

            $this->showModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->error('Error saving warehouse: ' . $e->getMessage());
        }
    }

    public function deleteWarehouse(Warehouse $warehouse)
    {
        try {
            if ($warehouse->inventory()->exists() || $warehouse->sales()->exists()) {
                $this->error('Cannot delete warehouse with existing inventory or sales records.');
                return;
            }

            $warehouse->delete();
            $this->success('Warehouse deleted successfully!');
        } catch (\Exception $e) {
            $this->error('Error deleting warehouse: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Warehouse $warehouse)
    {
        $warehouse->update(['is_active' => !$warehouse->is_active]);
        $status = $warehouse->is_active ? 'activated' : 'deactivated';
        $this->success("Warehouse {$status} successfully!");
    }

    public function generateCode()
    {
        $this->code = strtoupper(substr($this->name, 0, 4) . str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT));
    }

    public function clearFilters()
    {
        $this->reset(['search', 'typeFilter', 'statusFilter']);
    }

    private function resetForm()
    {
        $this->reset(['name', 'code', 'address', 'city', 'manager_name', 'phone', 'type', 'is_active']);
        $this->type = 'main';
        $this->is_active = true;
    }
}
