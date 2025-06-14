<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class CustomerManagement extends Component
{
    use WithPagination;
    use Toast;

    public $showModal = false;
    public $editMode = false;
    public $selectedCustomer = null;

    // Form fields
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $city = '';
    public $type = 'individual';
    public $customer_group_id = '';
    public $date_of_birth = '';
    public $gender = '';
    public $tax_id = '';
    public $credit_limit = 0.00;
    public $notes = '';
    public $is_active = true;

    // Search and filters
    public $search = '';
    public $typeFilter = '';
    public $groupFilter = '';
    public $statusFilter = '';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:customers,email',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:500',
        'city' => 'nullable|string|max:100',
        'type' => 'required|in:individual,business',
        'customer_group_id' => 'nullable|exists:customer_groups,id',
        'date_of_birth' => 'nullable|date|before:today',
        'gender' => 'nullable|in:male,female,other',
        'tax_id' => 'nullable|string|max:50',
        'credit_limit' => 'required|numeric|min:0',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $customers = Customer::with(['customerGroup', 'sales'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->orWhere('phone', 'like', '%' . $this->search . '%'))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->groupFilter, fn($q) => $q->where('customer_group_id', $this->groupFilter))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $customerGroups = CustomerGroup::where('is_active', true)->orderBy('name')->get();

        $filterOptions = [
            'types' => [
                ['value' => '', 'label' => 'All Types'],
                ['value' => 'individual', 'label' => 'Individual'],
                ['value' => 'business', 'label' => 'Business'],
            ],
            'groups' => $customerGroups->map(fn($g) => ['value' => $g->id, 'label' => $g->name]),
            'statuses' => [
                ['value' => '', 'label' => 'All Status'],
                ['value' => '1', 'label' => 'Active'],
                ['value' => '0', 'label' => 'Inactive'],
            ]
        ];

        // Summary statistics
        $totalCustomers = Customer::where('is_active', true)->count();
        $newThisMonth = Customer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();
        $totalSales = Customer::where('is_active', true)->sum('total_purchases');

        return view('livewire.sales.customer-management', [
            'customers' => $customers,
            'customerGroups' => $customerGroups,
            'filterOptions' => $filterOptions,
            'totalCustomers' => $totalCustomers,
            'newThisMonth' => $newThisMonth,
            'totalSales' => $totalSales,
        ])->layout('layouts.app', ['title' => 'Customer Management']);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->selectedCustomer = null;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function editCustomer(Customer $customer)
    {
        $this->selectedCustomer = $customer;
        $this->name = $customer->name;
        $this->email = $customer->email ?? '';
        $this->phone = $customer->phone ?? '';
        $this->address = $customer->address ?? '';
        $this->city = $customer->city ?? '';
        $this->type = $customer->type;
        $this->customer_group_id = $customer->customer_group_id;
        $this->date_of_birth = $customer->date_of_birth?->format('Y-m-d') ?? '';
        $this->gender = $customer->gender ?? '';
        $this->tax_id = $customer->tax_id ?? '';
        $this->credit_limit = $customer->credit_limit;
        $this->notes = $customer->notes ?? '';
        $this->is_active = $customer->is_active;
        $this->editMode = true;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function save()
    {
        if ($this->editMode) {
            $this->rules['email'] = 'nullable|email|unique:customers,email,' . $this->selectedCustomer->id;
        }

        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'email' => $this->email ?: null,
                'phone' => $this->phone ?: null,
                'address' => $this->address ?: null,
                'city' => $this->city ?: null,
                'type' => $this->type,
                'customer_group_id' => $this->customer_group_id ?: null,
                'date_of_birth' => $this->date_of_birth ?: null,
                'gender' => $this->gender ?: null,
                'tax_id' => $this->tax_id ?: null,
                'credit_limit' => $this->credit_limit ?: null,
                'notes' => $this->notes ?: null,
                'is_active' => $this->is_active,
            ];

            if ($this->editMode) {
                $this->selectedCustomer->update($data);
                $this->success('Customer updated successfully!');
            } else {
                Customer::create($data);
                $this->success('Customer created successfully!');
            }

            $this->showModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->error('Error saving customer: ' . $e->getMessage());
        }
    }

    public function deleteCustomer(Customer $customer)
    {
        try {
            if ($customer->sales()->exists()) {
                $this->error('Cannot delete customer with existing sales records.');
                return;
            }

            $customer->delete();
            $this->success('Customer deleted successfully!');
        } catch (\Exception $e) {
            $this->error('Error deleting customer: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Customer $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);
        $status = $customer->is_active ? 'activated' : 'deactivated';
        $this->success("Customer {$status} successfully!");
    }

    public function clearFilters()
    {
        $this->reset(['search', 'typeFilter', 'groupFilter', 'statusFilter']);
    }

    private function resetForm()
    {
        $this->reset([
            'name',
            'email',
            'phone',
            'address',
            'city',
            'type',
            'customer_group_id',
            'date_of_birth',
            'gender',
            'tax_id',
            'credit_limit',
            'notes',
            'is_active'
        ]);
        $this->type = 'individual';
        $this->is_active = true;
    }
}
