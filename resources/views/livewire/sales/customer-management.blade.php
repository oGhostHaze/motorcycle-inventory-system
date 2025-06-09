<div>
    {{-- Page Header --}}
    <x-mary-header title="Customer Management" subtitle="Manage customer accounts and relationships" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search customers..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" wire:click="openModal" class="btn-primary">
                Add Customer
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <x-mary-stat title="Total Customers" description="Active customers" value="{{ number_format($totalCustomers) }}"
            icon="o-users" color="text-primary" />

        <x-mary-stat title="New This Month" description="Recently added" value="{{ $newThisMonth }}" icon="o-user-plus"
            color="text-success" />

        <x-mary-stat title="Total Sales" description="Customer purchases" value="₱{{ number_format($totalSales, 2) }}"
            icon="o-banknotes" color="text-info" />
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <x-mary-select placeholder="All Types" :options="$filterOptions['types']" wire:model.live="typeFilter" option-value="value"
            option-label="label" />
        <x-mary-select placeholder="All Groups" :options="$filterOptions['groups']" wire:model.live="groupFilter" option-value="value"
            option-label="label" />
        <x-mary-select placeholder="All Status" :options="$filterOptions['statuses']" wire:model.live="statusFilter" option-value="value"
            option-label="label" />
        <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
            Clear Filters
        </x-mary-button>
    </div>

    {{-- Customers Table --}}
    <x-mary-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Group</th>
                        <th>Contact</th>
                        <th>Sales</th>
                        <th>Last Purchase</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div
                                            class="w-8 h-8 text-center uppercase pt-2 text-xs rounded-full bg-neutral text-neutral-content">
                                            <span>{{ substr($customer->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold">{{ $customer->name }}</div>
                                        @if ($customer->tax_id)
                                            <div class="text-sm text-gray-500">TIN: {{ $customer->tax_id }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <x-mary-badge value="{{ ucfirst($customer->type) }}"
                                    class="badge-{{ $customer->type === 'business' ? 'info' : 'secondary' }} badge-sm" />
                            </td>
                            <td>
                                <span class="text-sm">{{ $customer->customerGroup?->name ?? 'Default' }}</span>
                                @if ($customer->customerGroup?->discount_percentage)
                                    <div class="text-xs text-success">
                                        {{ $customer->customerGroup->discount_percentage }}% discount</div>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">
                                    @if ($customer->email)
                                        <div>{{ $customer->email }}</div>
                                    @endif
                                    @if ($customer->phone)
                                        <div class="text-gray-500">{{ $customer->phone }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div class="font-semibold">{{ $customer->total_orders }} orders</div>
                                    <div class="text-gray-500">₱{{ number_format($customer->total_purchases, 2) }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    @if ($customer->last_purchase_at)
                                        <div>{{ $customer->last_purchase_at->format('M d, Y') }}</div>
                                        <div class="text-gray-500">{{ $customer->last_purchase_at->diffForHumans() }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">Never</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <x-mary-badge value="{{ $customer->is_active ? 'Active' : 'Inactive' }}"
                                    class="badge-{{ $customer->is_active ? 'success' : 'error' }}" />
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                                        <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                                    </div>
                                    <ul tabindex="0"
                                        class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                                        <li><a wire:click="editCustomer({{ $customer->id }})">
                                                <x-heroicon-o-pencil class="w-4 h-4" /> Edit</a></li>
                                        <li><a wire:click="toggleStatus({{ $customer->id }})">
                                                <x-heroicon-o-{{ $customer->is_active ? 'x-mark' : 'check' }}
                                                    class="w-4 h-4" />
                                                {{ $customer->is_active ? 'Deactivate' : 'Activate' }}</a></li>
                                        <li><a href="#" class="text-info">
                                                <x-heroicon-o-eye class="w-4 h-4" /> View Sales</a></li>
                                        <li><a wire:click="deleteCustomer({{ $customer->id }})"
                                                wire:confirm="Are you sure?" class="text-error">
                                                <x-heroicon-o-trash class="w-4 h-4" /> Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="py-8">
                                    <x-heroicon-o-users class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-gray-500">No customers found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    </x-mary-card>

    {{-- Create/Edit Modal --}}
    <x-mary-modal wire:model="showModal" title="{{ $editMode ? 'Edit Customer' : 'Create New Customer' }}"
        subtitle="Manage customer information and details">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            {{-- Basic Information --}}
            <div class="space-y-4 md:col-span-2">
                <h4 class="text-lg font-semibold">Basic Information</h4>
            </div>

            <x-mary-input label="Full Name" wire:model="name" placeholder="Enter customer name" />
            <x-mary-input label="Email Address" wire:model="email" placeholder="customer@example.com" />

            <x-mary-input label="Phone Number" wire:model="phone" placeholder="Contact number" />
            <x-mary-select label="Customer Type" :options="[
                ['value' => 'individual', 'label' => 'Individual'],
                ['value' => 'business', 'label' => 'Business'],
            ]" wire:model="type" />

            {{-- Address Information --}}
            <div class="space-y-4 md:col-span-2">
                <h4 class="text-lg font-semibold">Address Information</h4>
            </div>

            <x-mary-textarea label="Address" wire:model="address" placeholder="Complete address" rows="2"
                class="md:col-span-2" />
            <x-mary-input label="City" wire:model="city" placeholder="City" />
            <x-mary-select label="Customer Group" :options="$customerGroups->map(fn($g) => ['value' => $g->id, 'label' => $g->name])" wire:model="customer_group_id"
                placeholder="Select group (optional)" />

            {{-- Additional Information --}}
            <div class="space-y-4 md:col-span-2">
                <h4 class="text-lg font-semibold">Additional Information</h4>
            </div>

            <x-mary-input label="Date of Birth" wire:model="date_of_birth" type="date" />
            <x-mary-select label="Gender" :options="[
                ['value' => 'male', 'label' => 'Male'],
                ['value' => 'female', 'label' => 'Female'],
                ['value' => 'other', 'label' => 'Other'],
            ]" wire:model="gender"
                placeholder="Select gender (optional)" />

            <x-mary-input label="Tax ID" wire:model="tax_id" placeholder="TIN or Tax ID" />
            <x-mary-input label="Credit Limit" wire:model="credit_limit" type="number" step="0.01"
                placeholder="0.00" />

            <x-mary-textarea label="Notes" wire:model="notes" placeholder="Additional customer notes"
                rows="3" class="md:col-span-2" />

            <div class="flex items-center md:col-span-2">
                <x-mary-checkbox label="Active Customer" wire:model="is_active" />
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showModal', false)" />
            <x-mary-button label="{{ $editMode ? 'Update Customer' : 'Create Customer' }}" wire:click="save"
                class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>
