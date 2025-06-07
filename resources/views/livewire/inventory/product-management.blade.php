<div>
    {{-- Page Header --}}
    <x-mary-header title="Product Management" subtitle="Manage your inventory products" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search products..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" @click="$wire.openModal()">
                Add Product
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-5">
        <x-mary-select placeholder="All Categories" :options="$filterOptions['categories']" wire:model.live="categoryFilter" />
        <x-mary-select placeholder="All Brands" :options="$filterOptions['brands']" wire:model.live="brandFilter" />
        <x-mary-select placeholder="All Status" :options="$filterOptions['statuses']" wire:model.live="statusFilter" />
        <x-mary-select placeholder="All Stock" :options="$filterOptions['stock']" wire:model.live="stockFilter" />
        <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
            Clear Filters
        </x-mary-button>
    </div>

    {{-- Products Table --}}
    <x-mary-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th class="w-32">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <div>
                                    <div class="font-bold">{{ $product->name }}</div>
                                    <div class="text-sm opacity-50">
                                        SKU: {{ $product->sku }}
                                        @if ($product->barcode)
                                            • Barcode: {{ $product->barcode }}
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $product->category?->name }}</span>
                                @if ($product->subcategory)
                                    <div class="text-xs text-gray-500">{{ $product->subcategory->name }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="text-sm">{{ $product->brand?->name ?? 'No Brand' }}</span>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div class="font-semibold">₱{{ number_format($product->selling_price, 2) }}</div>
                                    <div class="text-xs text-gray-500">Cost:
                                        ₱{{ number_format($product->cost_price, 2) }}</div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $totalStock = $product->total_stock;
                                    $isLowStock = $product->isLowStock();
                                @endphp
                                <div class="text-sm">
                                    <div
                                        class="font-semibold {{ $totalStock == 0 ? 'text-error' : ($isLowStock ? 'text-warning' : 'text-success') }}">
                                        {{ $totalStock }} units
                                    </div>
                                    @if ($isLowStock && $totalStock > 0)
                                        <x-mary-badge value="Low Stock" class="badge-warning badge-xs" />
                                    @elseif($totalStock == 0)
                                        <x-mary-badge value="Out of Stock" class="badge-error badge-xs" />
                                    @endif
                                </div>
                            </td>
                            <td>
                                <x-mary-badge value="{{ ucfirst($product->status) }}"
                                    class="badge-{{ $product->status === 'active' ? 'success' : ($product->status === 'inactive' ? 'warning' : 'error') }}" />
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <x-mary-button icon="o-eye" wire:click="viewProduct({{ $product->id }})"
                                        class="btn-ghost btn-xs" tooltip="View Details" />
                                    <x-mary-button icon="o-pencil" wire:click="editProduct({{ $product->id }})"
                                        class="btn-ghost btn-xs" tooltip="Edit" />
                                    <x-mary-button icon="o-trash" wire:click="deleteProduct({{ $product->id }})"
                                        wire:confirm="Are you sure you want to delete this product?"
                                        class="btn-ghost btn-xs text-error" tooltip="Delete" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="py-8">
                                    <x-heroicon-o-cube class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-gray-500">No products found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </x-mary-card>

    {{-- Create/Edit Modal --}}
    <x-mary-modal wire:model="showModal" title="{{ $editMode ? 'Edit Product' : 'Create New Product' }}"
        subtitle="Manage product information and inventory" class="w-11/12 max-w-5xl">

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Basic Information --}}
            <div class="space-y-4">
                <h4 class="text-lg font-semibold">Basic Information</h4>

                <x-mary-input label="Product Name" wire:model="name" placeholder="Enter product name" />

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-mary-input label="SKU" wire:model="sku" placeholder="Product SKU">
                            <x-slot:append>
                                <x-mary-button icon="o-sparkles" wire:click="generateSku" class="btn-outline btn-sm"
                                    tooltip="Generate SKU" />
                            </x-slot:append>
                        </x-mary-input>
                    </div>
                    <div>
                        <x-mary-input label="Barcode" wire:model="barcode" placeholder="Product barcode">
                            <x-slot:append>
                                <x-mary-button icon="o-qr-code" wire:click="generateBarcode" class="btn-outline btn-sm"
                                    tooltip="Generate Barcode" />
                            </x-slot:append>
                        </x-mary-input>
                    </div>
                </div>

                <x-mary-textarea label="Description" wire:model="description" placeholder="Product description"
                    rows="3" />

                <div class="grid grid-cols-2 gap-3">
                    <x-mary-select label="Category" :options="$categories->map(fn($cat) => ['value' => $cat->id, 'label' => $cat->name])" wire:model.live="category_id"
                        placeholder="Select category" />

                    @if ($subcategories->count() > 0)
                        <x-mary-select label="Subcategory" :options="$subcategories->map(fn($sub) => ['value' => $sub->id, 'label' => $sub->name])" wire:model="subcategory_id"
                            placeholder="Select subcategory" />
                    @endif
                </div>

                <x-mary-select label="Brand" :options="$brands->map(fn($brand) => ['value' => $brand->id, 'label' => $brand->name])" wire:model="product_brand_id"
                    placeholder="Select brand" />
            </div>

            {{-- Pricing & Details --}}
            <div class="space-y-4">
                <h4 class="text-lg font-semibold">Pricing & Details</h4>

                <div class="grid grid-cols-2 gap-3">
                    <x-mary-input label="Part Number" wire:model="part_number" placeholder="Manufacturer part #" />
                    <x-mary-input label="OEM Number" wire:model="oem_number" placeholder="OEM part #" />
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <x-mary-input label="Cost Price" wire:model="cost_price" type="number" step="0.01"
                        placeholder="0.00" />
                    <x-mary-input label="Selling Price" wire:model="selling_price" type="number" step="0.01"
                        placeholder="0.00" />
                    <x-mary-input label="Wholesale Price" wire:model="wholesale_price" type="number" step="0.01"
                        placeholder="0.00" />
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <x-mary-input label="Weight (kg)" wire:model="weight" type="number" step="0.001"
                        placeholder="0.000" />
                    <x-mary-input label="Color" wire:model="color" placeholder="Product color" />
                    <x-mary-input label="Size" wire:model="size" placeholder="Product size" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <x-mary-input label="Material" wire:model="material" placeholder="Product material" />
                    <x-mary-input label="Warranty (months)" wire:model="warranty_months" type="number"
                        placeholder="0" />
                </div>

                <div class="flex gap-4">
                    <x-mary-checkbox label="Track Serial Numbers" wire:model="track_serial" />
                    <x-mary-checkbox label="Track Warranty" wire:model="track_warranty" />
                </div>
            </div>
        </div>

        {{-- Stock Management --}}
        <div class="mt-6">
            <h4 class="mb-4 text-lg font-semibold">Stock Management</h4>

            <div class="grid grid-cols-4 gap-3 mb-4">
                <x-mary-input label="Min Stock Level" wire:model="min_stock_level" type="number" placeholder="0" />
                <x-mary-input label="Max Stock Level" wire:model="max_stock_level" type="number" placeholder="0" />
                <x-mary-input label="Reorder Point" wire:model="reorder_point" type="number" placeholder="0" />
                <x-mary-input label="Reorder Quantity" wire:model="reorder_quantity" type="number"
                    placeholder="0" />
            </div>

            {{-- Warehouse Stock Levels --}}
            <div class="p-4 border rounded-lg">
                <h5 class="mb-3 font-medium">Warehouse Stock Levels</h5>
                <div class="space-y-3">
                    @foreach ($warehouseStock as $warehouseId => $stock)
                        <div class="grid items-end grid-cols-3 gap-3">
                            <div>
                                <label class="text-sm font-medium">{{ $stock['warehouse_name'] }}</label>
                            </div>
                            <x-mary-input label="Quantity" wire:model="warehouseStock.{{ $warehouseId }}.quantity"
                                type="number" placeholder="0" />
                            <x-mary-input label="Location" wire:model="warehouseStock.{{ $warehouseId }}.location"
                                placeholder="e.g., A1-B2" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Additional Fields --}}
        <div class="grid grid-cols-2 gap-6 mt-6">
            <div>
                <x-mary-select label="Status" :options="[
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                    ['value' => 'discontinued', 'label' => 'Discontinued'],
                ]" wire:model="status" />
            </div>
            <div>
                <x-mary-file label="Product Image" wire:model="productImage" accept="image/*" />
            </div>
        </div>

        <div class="mt-4">
            <x-mary-textarea label="Internal Notes" wire:model="internal_notes"
                placeholder="Internal notes (not visible to customers)" rows="2" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showModal', false)" />
            <x-mary-button label="{{ $editMode ? 'Update Product' : 'Create Product' }}" wire:click="save"
                class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>
