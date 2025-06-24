<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryLocation;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class InventoryLocationManagement extends Component
{
    use WithPagination, Toast;

    public $showModal = false;
    public $editMode = false;
    public $selectedLocation = null;

    // Form fields
    public $code = '';
    public $name = '';
    public $description = '';
    public $zone = '';
    public $section = '';
    public $level = '';
    public $sort_order = '';
    public $is_active = true;

    // Bulk creation
    public $showBulkModal = false;
    public $bulkZone = '';
    public $bulkPrefix = '';
    public $bulkStart = 1;
    public $bulkEnd = 10;

    // Filters
    public $search = '';
    public $zoneFilter = '';
    public $statusFilter = '';

    protected $rules = [
        'code' => 'required|string|max:20|unique:inventory_locations,code',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'zone' => 'nullable|string|max:50',
        'section' => 'nullable|string|max:50',
        'level' => 'nullable|string|max:50',
        'sort_order' => 'nullable|integer|min:0',
        'is_active' => 'boolean'
    ];

    public function render()
    {
        $locations = InventoryLocation::withCount('inventories')
            ->when($this->search, function ($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->zoneFilter, fn($q) => $q->where('zone', $this->zoneFilter))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter))
            ->ordered()
            ->paginate(15);

        $zones = InventoryLocation::distinct()->pluck('zone')->filter()->sort()->values();

        return view('livewire.inventory.inventory-location-management', [
            'locations' => $locations,
            'zones' => $zones
        ]);
    }

    public function openModal()
    {
        $this->reset(['code', 'name', 'description', 'zone', 'section', 'level', 'sort_order', 'is_active']);
        $this->is_active = true;
        $this->editMode = false;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function save()
    {
        if ($this->editMode) {
            $this->rules['code'] = 'required|string|max:20|unique:inventory_locations,code,' . $this->selectedLocation->id;
        }

        $this->validate();

        $data = [
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'description' => $this->description,
            'zone' => $this->zone ? strtoupper($this->zone) : null,
            'section' => $this->section,
            'level' => $this->level,
            'sort_order' => $this->sort_order ?: 0,
            'is_active' => $this->is_active
        ];

        if ($this->editMode) {
            $this->selectedLocation->update($data);
            $this->success('Location updated successfully!');
        } else {
            InventoryLocation::create($data);
            $this->success('Location created successfully!');
        }

        $this->showModal = false;
    }

    public function editLocation(InventoryLocation $location)
    {
        $this->selectedLocation = $location;
        $this->code = $location->code;
        $this->name = $location->name;
        $this->description = $location->description;
        $this->zone = $location->zone;
        $this->section = $location->section;
        $this->level = $location->level;
        $this->sort_order = $location->sort_order;
        $this->is_active = $location->is_active;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function deleteLocation(InventoryLocation $location)
    {
        if ($location->inventories()->exists()) {
            $this->error('Cannot delete location that has inventory items assigned to it.');
            return;
        }

        $location->delete();
        $this->success('Location deleted successfully!');
    }

    public function createBulkLocations()
    {
        $this->validate([
            'bulkZone' => 'required|string|max:10',
            'bulkPrefix' => 'required|string|max:10',
            'bulkStart' => 'required|integer|min:1',
            'bulkEnd' => 'required|integer|min:1|gte:bulkStart',
        ]);

        $created = 0;
        $errors = 0;

        for ($i = $this->bulkStart; $i <= $this->bulkEnd; $i++) {
            $code = strtoupper($this->bulkPrefix) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $name = ucfirst(strtolower($this->bulkZone)) . ' ' . $this->bulkPrefix . ' ' . $i;

            try {
                InventoryLocation::create([
                    'code' => $code,
                    'name' => $name,
                    'zone' => strtoupper($this->bulkZone),
                    'section' => $this->bulkPrefix,
                    'sort_order' => $i,
                    'is_active' => true
                ]);
                $created++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        $this->success("Created {$created} locations successfully!" . ($errors > 0 ? " {$errors} duplicates skipped." : ""));
        $this->showBulkModal = false;
    }

    public function migrateLocations()
    {
        $migrated = 0;
        $skipped = 0;

        // Get all unique legacy locations
        $legacyLocations = \DB::table('inventories')
            ->whereNotNull('location_legacy')
            ->where('location_legacy', '!=', '')
            ->whereNull('inventory_location_id')
            ->distinct()
            ->pluck('location_legacy');

        foreach ($legacyLocations as $legacyLocation) {
            // Check if location already exists
            $existing = InventoryLocation::where('code', $legacyLocation)->first();

            if (!$existing) {
                // Create new location
                $location = InventoryLocation::create([
                    'code' => $legacyLocation,
                    'name' => $legacyLocation,
                    'description' => 'Migrated from legacy location',
                    'is_active' => true
                ]);

                // Update inventories to use new location
                \DB::table('inventories')
                    ->where('location_legacy', $legacyLocation)
                    ->update(['inventory_location_id' => $location->id]);

                $migrated++;
            } else {
                // Update inventories to use existing location
                \DB::table('inventories')
                    ->where('location_legacy', $legacyLocation)
                    ->update(['inventory_location_id' => $existing->id]);

                $skipped++;
            }
        }

        $this->success("Migration completed! Created {$migrated} new locations, used {$skipped} existing locations.");
    }
}
