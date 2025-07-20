<?php

namespace App\Livewire\Admin;

use Mary\Traits\Toast;
use Livewire\Component;
use App\Models\Category;
use App\Models\Subcategory;
use Livewire\WithPagination;
use App\Models\ProductService;

class ServiceManagement extends Component
{
    use WithPagination;
    use Toast;

    public $search = '';
    public $statusFilter = '';

    // Form properties
    public $showModal = false;
    public $editingService = null;
    public $name = '';
    public $price = '';
    public $status = 'active';
    public $notes = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'status' => 'required|in:active,inactive',
        'notes' => 'nullable|string',
    ];

    public function render()
    {
        $services = ProductService::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.service-management', [
            'services' => $services,
        ])->layout('layouts.app', ['title' => 'Service Management']);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal($serviceId)
    {
        $service = ProductService::findOrFail($serviceId);
        $this->editingService = $service;

        $this->name = $service->name;
        $this->price = $service->price;
        $this->status = $service->status;
        $this->notes = $service->notes;

        $this->showModal = true;
    }

    public function saveService()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'price' => $this->price,
            'status' => $this->status,
            'notes' => $this->notes,
        ];

        if ($this->editingService) {
            $this->editingService->update($data);
            $this->success('Service updated successfully!');
        } else {
            ProductService::create($data);
            $this->success('Service created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function deleteService($serviceId)
    {
        $service = ProductService::findOrFail($serviceId);

        // Check if service is used in any sales
        if ($service->saleItems()->exists()) {
            $this->error('Cannot delete service. It has been used in sales.');
            return;
        }

        $service->delete();
        $this->success('Service deleted successfully!');
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter']);
    }

    protected function resetForm()
    {
        $this->reset([
            'editingService',
            'name',
            'price',
            'status',
            'notes'
        ]);

        // Set defaults
        $this->status = 'active';
    }
}
