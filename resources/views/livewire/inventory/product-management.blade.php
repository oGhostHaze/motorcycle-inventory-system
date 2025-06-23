{{-- Enhanced Product Management Form with MaryUI Choices --}}
<div>
    {{-- Header Section --}}
    <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold">Product Management</h1>
            <p class="text-gray-600">Manage your product inventory and information</p>
        </div>
        <x-mary-button label="Add Product" wire:click="openModal" class="btn-primary" icon="o-plus" />
    </div>

    {{-- Filters Section --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-6">
        <x-mary-input label="Search" wire:model.live.debounce.300ms="search" placeholder="Search products..."
            icon="o-magnifying-glass" />

        <x-mary-select label="Category" :options="$filterOptions['categories']" wire:model.live="categoryFilter" placeholder="All Categories"
            option-value="value" option-label="label" />

        <x-mary-select label="Brand" :options="$filterOptions['brands']" wire:model.live="brandFilter" placeholder="All Brands"
            option-value="value" option-label="label" />

        <x-mary-select label="Status" :options="$filterOptions['statuses']" wire:model.live="statusFilter" placeholder="All Status"
            option-value="value" option-label="label" />

        <x-mary-select label="Stock" :options="$filterOptions['stock']" wire:model.live="stockFilter" placeholder="All Stock"
            option-value="value" option-label="label" />

        <div class="flex items-end">
            <x-mary-button label="Clear" wire:click="clearFilters" class="w-full btn-outline" />
        </div>
    </div>

    {{-- Products Table --}}
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Stock</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div>
                                <div class="font-semibold">{{ $product->name }}</div>
                                <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div>{{ $product->category?->name ?? 'No Category' }}</div>
                                @if ($product->subcategory)
                                    <div class="text-sm text-gray-500">{{ $product->subcategory->name }}</div>
                                @endif
                            </div>
                        </td>
                        <td>{{ $product->brand?->name ?? 'No Brand' }}</td>
                        <td>
                            <x-mary-badge value="{{ $product->total_stock ?? 0 }}"
                                class="badge-{{ ($product->total_stock ?? 0) > 0 ? 'success' : 'error' }}" />
                        </td>
                        <td>₱{{ number_format($product->selling_price, 2) }}</td>
                        <td>
                            <x-mary-badge value="{{ ucfirst($product->status) }}"
                                class="badge-{{ $product->status === 'active' ? 'success' : 'warning' }}" />
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <x-mary-button icon="o-eye" wire:click="viewProduct({{ $product->id }})"
                                    class="btn-ghost btn-sm" tooltip="View" />
                                <x-mary-button icon="o-pencil" wire:click="editProduct({{ $product->id }})"
                                    class="btn-ghost btn-sm" tooltip="Edit" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-500">No products found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $products->links() }}
    </div>

    {{-- Create/Edit Product Modal --}}
    <x-mary-modal wire:model="showModal" title="{{ $editMode ? 'Edit Product' : 'Create New Product' }}"
        subtitle="Manage product information and inventory" box-class="max-w-7xl">

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Basic Information --}}
            <div class="space-y-4">
                <h4 class="text-lg font-semibold">Basic Information</h4>

                <x-mary-input label="Product Name" wire:model="name" placeholder="Enter product name"
                    hint="Include compatible models separated with a slash `/` e.g NMAX/PCX/M3" />

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-mary-input label="SKU" wire:model="sku" placeholder="Product SKU">
                            <x-slot:append>
                                <x-mary-button icon="o-sparkles" wire:click="generateSku" class="btn-outline"
                                    tooltip="Generate SKU" />
                            </x-slot:append>
                        </x-mary-input>
                    </div>
                    <div>
                        <x-mary-input label="Barcode" wire:model="barcode" placeholder="Product barcode">
                            <x-slot:append>
                                <x-mary-button icon="o-qr-code" wire:click="generateBarcode" class="btn-outline"
                                    tooltip="Generate Barcode" />
                            </x-slot:append>
                        </x-mary-input>
                    </div>
                </div>

                <x-mary-textarea label="Description" wire:model="description" placeholder="Product description"
                    rows="3" />

                {{-- Enhanced Category Selection with Choices --}}
                <div class="space-y-3">
                    <x-mary-choices-offline label="Category *" wire:model.live="category_id" :options="$categoriesSearchable"
                        placeholder="Search and select category..." searchable single clearable height="max-h-48"
                        hint="Required field">

                        {{-- Custom item display --}}
                        @scope('item', $category)
                            <div class="flex items-center gap-3 p-2">
                                <div class="flex-shrink-0">
                                    @if ($category->icon ?? false)
                                        <x-mary-icon :name="$category->icon" class="w-6 h-6 text-primary" />
                                    @else
                                        <x-mary-icon name="o-folder" class="w-6 h-6 text-primary" />
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">{{ $category->name }}</div>
                                    @if ($category->description)
                                        <div class="text-sm text-gray-500">{{ $category->description }}</div>
                                    @endif
                                </div>
                                <div class="flex-shrink-0">
                                    <x-mary-badge value="{{ $category->products_count ?? 0 }}"
                                        class="badge-ghost badge-sm" />
                                </div>
                            </div>
                        @endscope

                        {{-- Custom selection display --}}
                        @scope('selection', $category)
                            {{ $category->name }}
                        @endscope
                    </x-mary-choices-offline>

                    {{-- Enhanced Subcategory Selection --}}
                    @if ($category_id && $subcategoriesSearchable && $subcategoriesSearchable->count() > 0)
                        <x-mary-choices-offline label="Subcategory" wire:model="subcategory_id" :options="$subcategoriesSearchable"
                            placeholder="Search and select subcategory..." searchable single clearable
                            height="max-h-48" hint="Optional subcategory">

                            {{-- Custom item display --}}
                            @scope('item', $subcategory)
                                <div class="flex items-center gap-3 p-2">
                                    <div class="flex-shrink-0">
                                        <x-mary-icon name="o-folder-open" class="w-5 h-5 text-secondary" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium">{{ $subcategory->name }}</div>
                                        @if ($subcategory->description)
                                            <div class="text-sm text-gray-500">{{ $subcategory->description }}</div>
                                        @endif
                                    </div>
                                    <div class="flex-shrink-0">
                                        <x-mary-badge value="{{ $subcategory->products_count ?? 0 }}"
                                            class="badge-ghost badge-sm" />
                                    </div>
                                </div>
                            @endscope

                            {{-- Custom selection display --}}
                            @scope('selection', $subcategory)
                                {{ $subcategory->name }}
                            @endscope
                        </x-mary-choices-offline>
                    @endif
                </div>

                {{-- Enhanced Brand Selection with Choices --}}
                <x-mary-choices-offline label="Brand" wire:model="product_brand_id" :options="$brandsSearchable"
                    placeholder="Search and select brand..." searchable single clearable height="max-h-48"
                    hint="Select product brand">

                    {{-- Custom item display --}}
                    @scope('item', $brand)
                        <div class="flex items-center gap-3 p-2">
                            <div class="flex-shrink-0">
                                @if ($brand->logo ?? false)
                                    <img src="{{ Storage::url($brand->logo) }}"
                                        class="object-cover w-6 h-6 rounded-full" />
                                @else
                                    <x-mary-icon name="o-building-storefront" class="w-6 h-6 text-accent" />
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">{{ $brand->name }}</div>
                                @if ($brand->description)
                                    <div class="text-sm text-gray-500">{{ $brand->description }}</div>
                                @endif
                            </div>
                            <div class="flex-shrink-0">
                                <x-mary-badge value="{{ $brand->products_count ?? 0 }}" class="badge-ghost badge-sm" />
                            </div>
                        </div>
                    @endscope

                    {{-- Custom selection display --}}
                    @scope('selection', $brand)
                        {{ $brand->name }}
                    @endscope
                </x-mary-choices-offline>
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

                <div class="grid grid-cols-1 gap-3">
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
                    ['id' => 'active', 'name' => 'Active'],
                    ['id' => 'inactive', 'name' => 'Inactive'],
                    ['id' => 'discontinued', 'name' => 'Discontinued'],
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

    {{-- View Product Details Modal --}}
    <x-mary-modal wire:model="showViewModal" title="Product Details" subtitle="{{ $selectedProduct?->name }}"
        box-class="max-w-7xl">

        @if ($selectedProduct)
            <div class="space-y-6">
                {{-- Basic Information --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold">Basic Information</h4>
                        <div class="space-y-2">
                            <div><strong>Name:</strong> {{ $selectedProduct->name }}</div>
                            <div><strong>SKU:</strong> {{ $selectedProduct->sku }}</div>
                            @if ($selectedProduct->barcode)
                                <div><strong>Barcode:</strong> {{ $selectedProduct->barcode }}</div>
                            @endif
                            <div><strong>Category:</strong> {{ $selectedProduct->category?->name ?? 'No category' }}
                            </div>
                            @if ($selectedProduct->subcategory)
                                <div><strong>Subcategory:</strong> {{ $selectedProduct->subcategory->name }}</div>
                            @endif
                            <div><strong>Brand:</strong> {{ $selectedProduct->brand?->name ?? 'No brand' }}</div>
                            <div><strong>Status:</strong>
                                <x-mary-badge value="{{ ucfirst($selectedProduct->status) }}"
                                    class="badge-{{ $selectedProduct->status === 'active' ? 'success' : 'warning' }}" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold">Pricing & Stock</h4>
                        <div class="space-y-2">
                            <div><strong>Cost Price:</strong> ₱{{ number_format($selectedProduct->cost_price, 2) }}
                            </div>
                            <div><strong>Selling Price:</strong>
                                ₱{{ number_format($selectedProduct->selling_price, 2) }}</div>
                            @if ($selectedProduct->wholesale_price)
                                <div><strong>Wholesale Price:</strong>
                                    ₱{{ number_format($selectedProduct->wholesale_price, 2) }}</div>
                            @endif
                            <div><strong>Total Stock:</strong> {{ $selectedProduct->total_stock }} units</div>
                            <div><strong>Min Stock Level:</strong> {{ $selectedProduct->min_stock_level ?? 'Not set' }}
                            </div>
                            @if ($selectedProduct->max_stock_level)
                                <div><strong>Max Stock Level:</strong> {{ $selectedProduct->max_stock_level }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Description & Specifications --}}
                @if ($selectedProduct->description)
                    <div>
                        <h4 class="mb-3 text-lg font-semibold">Product Specifications</h4>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @if ($selectedProduct->description)
                                <div>
                                    <strong>Description:</strong>
                                    <p class="mt-1 text-sm text-gray-600">{{ $selectedProduct->description }}</p>
                                </div>
                            @endif

                            <div class="space-y-2">
                                @if ($selectedProduct->warranty_months)
                                    <div><strong>Warranty:</strong> {{ $selectedProduct->warranty_months }} months
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Warehouse Stock Levels --}}
                @if ($selectedProduct->inventory->count() > 0)
                    <div>
                        <h4 class="mb-3 text-lg font-semibold">Stock by Warehouse</h4>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Warehouse</th>
                                        <th>On Hand</th>
                                        <th>Reserved</th>
                                        <th>Available</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedProduct->inventory as $inventory)
                                        <tr>
                                            <td>{{ $inventory->warehouse->name }}</td>
                                            <td class="font-semibold">{{ $inventory->quantity_on_hand }}</td>
                                            <td class="text-warning">{{ $inventory->quantity_reserved }}</td>
                                            <td class="font-semibold text-success">
                                                {{ $inventory->quantity_available }}</td>
                                            <td>{{ $inventory->location ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Status & Tracking --}}
                <div>
                    <h4 class="mb-3 text-lg font-semibold">Status & Settings</h4>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <strong>Status:</strong>
                            <x-mary-badge value="{{ ucfirst($selectedProduct->status) }}"
                                class="badge-{{ $selectedProduct->status === 'active' ? 'success' : 'warning' }}" />
                        </div>
                        <div>
                            <strong>Serial Tracking:</strong>
                            <x-mary-badge value="{{ $selectedProduct->track_serial ? 'Yes' : 'No' }}"
                                class="badge-{{ $selectedProduct->track_serial ? 'success' : 'ghost' }}" />
                        </div>
                        <div>
                            <strong>Warranty Tracking:</strong>
                            <x-mary-badge value="{{ $selectedProduct->track_warranty ? 'Yes' : 'No' }}"
                                class="badge-{{ $selectedProduct->track_warranty ? 'success' : 'ghost' }}" />
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Close" wire:click="$set('showViewModal', false)" />
            @if ($selectedProduct)
                <x-mary-button label="Edit Product" wire:click="editProduct({{ $selectedProduct->id }})"
                    class="btn-primary" />
            @endif
        </x-slot:actions>
    </x-mary-modal>
</div>
