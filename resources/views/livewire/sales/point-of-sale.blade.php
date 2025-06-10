<div class="min-h-screen bg-base-200">
    {{-- Header with Shift Status --}}
    <div class="p-4 shadow-sm bg-base-100">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold">Point of Sale</h1>
                @if ($currentShift)
                    <div class="flex items-center space-x-2">
                        <x-mary-badge value="Shift: {{ $currentShift->shift_number }}" class="badge-success" />
                        <span class="text-sm text-gray-600">
                            Started: {{ $currentShift->started_at->format('H:i') }}
                        </span>
                    </div>
                @else
                    <x-mary-badge value="No Active Shift" class="badge-error" />
                @endif
            </div>
            <div class="flex items-center space-x-2">
                @if ($currentShift)
                    <div class="text-right">
                        <div class="text-sm font-medium">{{ $currentShift->total_transactions }} transactions</div>
                        <div class="text-sm text-gray-600">₱{{ number_format($currentShift->total_sales, 2) }} total
                        </div>
                    </div>
                @else
                    <x-mary-button icon="o-play" wire:click="openStartShiftModal" class="btn-primary">
                        Start Shift
                    </x-mary-button>
                @endif
                <x-mary-button icon="o-arrow-left" link="{{ route('dashboard') }}" class="btn-ghost">
                    Dashboard
                </x-mary-button>
            </div>
        </div>
    </div>

    @if (!$currentShift)
        {{-- No Active Shift Warning --}}
        <div class="p-4">
            <x-mary-alert title="No Active Shift"
                description="You must start a shift before processing sales. Click 'Start Shift' to begin."
                icon="o-exclamation-triangle" class="alert-warning">
                <x-slot:actions>
                    <x-mary-button label="Start Shift" wire:click="openStartShiftModal" class="btn-primary" />
                </x-slot:actions>
            </x-mary-alert>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 p-4 lg:grid-cols-12">
        {{-- Left Panel - Product Search & Cart --}}
        <div class="space-y-4 lg:col-span-8">
            {{-- Product Search --}}
            <x-mary-card title="Product Search" class="{{ !$currentShift ? 'opacity-50' : '' }}">
                <div class="space-y-4">
                    {{-- Search Input --}}
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <x-mary-input placeholder="Search by name, SKU, or barcode..."
                                wire:model.live.debounce="searchProduct" icon="o-magnifying-glass" :disabled="!$currentShift" />
                        </div>
                        <x-mary-button icon="o-qr-code" wire:click="openBarcodeModal" class="btn-secondary"
                            tooltip="Barcode Scanner" :disabled="!$currentShift" />
                    </div>

                    {{-- Search Results --}}
                    @if (count($searchResults) > 0)
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                            @foreach ($searchResults as $product)
                                <div class="p-3 transition-colors border rounded-lg cursor-pointer hover:bg-primary/10 hover:border-primary"
                                    wire:click="addToCart({{ $product['id'] }})">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-medium">{{ $product['name'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $product['sku'] }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-bold">₱{{ number_format($product['selling_price'], 2) }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Stock: {{ $product['inventory'][0]['quantity_available'] ?? 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-mary-card>

            {{-- Shopping Cart --}}
            <x-mary-card title="Shopping Cart ({{ count($cartItems) }} items)"
                class="{{ !$currentShift ? 'opacity-50' : '' }}">
                @if (count($cartItems) > 0)
                    <div class="space-y-3">
                        @foreach ($cartItems as $key => $item)
                            <div class="flex items-center gap-4 p-3 border rounded-lg bg-base-50">
                                <div class="flex-1">
                                    <div class="font-medium">{{ $item['name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $item['sku'] }}</div>
                                </div>

                                {{-- Quantity Controls --}}
                                <div class="flex items-center gap-2">
                                    <x-mary-button icon="o-minus"
                                        wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})"
                                        class="btn-xs btn-ghost" :disabled="!$currentShift" />
                                    <x-mary-input wire:model.blur="cartItems.{{ $key }}.quantity"
                                        wire:change="updateQuantity('{{ $key }}', $event.target.value)"
                                        class="w-16 text-center input-xs" :disabled="!$currentShift" />
                                    <x-mary-button icon="o-plus"
                                        wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                        class="btn-xs btn-ghost" :disabled="!$currentShift" />
                                </div>

                                {{-- Price --}}
                                <div class="w-24">
                                    <x-mary-input wire:model.blur="cartItems.{{ $key }}.price"
                                        wire:change="updatePrice('{{ $key }}', $event.target.value)"
                                        class="text-right input-xs" :disabled="!$currentShift" />
                                </div>

                                {{-- Subtotal --}}
                                <div class="w-20 font-bold text-right">
                                    ₱{{ number_format($item['subtotal'], 2) }}
                                </div>

                                {{-- Remove Button --}}
                                <x-mary-button icon="o-trash" wire:click="removeFromCart('{{ $key }}')"
                                    class="btn-xs btn-ghost text-error" :disabled="!$currentShift" />
                            </div>
                        @endforeach

                        {{-- Cart Actions --}}
                        <div class="flex gap-2 pt-4 border-t">
                            <x-mary-button label="Clear Cart" wire:click="clearCart" class="btn-ghost btn-sm"
                                :disabled="!$currentShift" />
                            <x-mary-button label="Hold Sale" wire:click="openHoldSaleModal" class="btn-warning btn-sm"
                                :disabled="!$currentShift" />
                            <x-mary-button label="Held Sales" wire:click="openHeldSalesModal" class="btn-info btn-sm"
                                :disabled="!$currentShift" />
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-shopping-cart class="w-12 h-12 mx-auto text-gray-400" />
                        <p class="mt-2 text-gray-500">Cart is empty</p>
                        <p class="text-sm text-gray-400">
                            {{ $currentShift ? 'Search for products to add to cart' : 'Start a shift to begin adding products' }}
                        </p>
                        <x-mary-button label="Held Sales" wire:click="openHeldSalesModal" class="btn-info btn-sm"
                            :disabled="!$currentShift" />
                    </div>
                @endif
            </x-mary-card>
        </div>

        {{-- Right Panel - Customer & Checkout --}}
        <div class="space-y-4 lg:col-span-4">
            {{-- Customer Selection --}}
            <x-mary-card title="Customer" class="{{ !$currentShift ? 'opacity-50' : '' }}">
                <div class="space-y-3">
                    @if ($selectedCustomer)
                        @php $customer = \App\Models\Customer::find($selectedCustomer) @endphp
                        <div class="p-3 border rounded-lg bg-primary/10">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $customer->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $customer->email }}</div>
                                </div>
                                <x-mary-button icon="o-x-mark" wire:click="$set('selectedCustomer', null)"
                                    class="btn-xs btn-ghost" :disabled="!$currentShift" />
                            </div>
                        </div>
                    @else
                        <div class="text-center text-gray-500">
                            <p>Walk-in Customer</p>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <x-mary-button label="Search Customer" wire:click="openSearchCustomerModal"
                            class="flex-1 btn-outline btn-sm" :disabled="!$currentShift" />
                        <x-mary-button label="New Customer" wire:click="openCustomerModal"
                            class="flex-1 btn-primary btn-sm" :disabled="!$currentShift" />
                    </div>
                </div>
            </x-mary-card>

            {{-- Order Summary --}}
            <x-mary-card title="Order Summary" class="{{ !$currentShift ? 'opacity-50' : '' }}">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span class="font-medium">₱{{ number_format($subtotal, 2) }}</span>
                    </div>

                    @if ($discountAmount > 0)
                        <div class="flex justify-between text-success">
                            <span>Discount:</span>
                            <span class="font-medium">-₱{{ number_format($discountAmount, 2) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <span>Tax ({{ $taxRate * 100 }}%):</span>
                        <span class="font-medium">₱{{ number_format($taxAmount, 2) }}</span>
                    </div>

                    <div class="flex justify-between pt-3 text-xl font-bold border-t">
                        <span>Total:</span>
                        <span>₱{{ number_format($totalAmount, 2) }}</span>
                    </div>

                    <div class="space-y-2">
                        <x-mary-button label="Apply Discount" wire:click="openDiscountModal"
                            class="w-full btn-warning btn-sm" :disabled="!$currentShift" />
                        @if ($discountAmount > 0)
                            <x-mary-button label="Remove Discount" wire:click="removeDiscount"
                                class="w-full btn-ghost btn-sm" :disabled="!$currentShift" />
                        @endif
                    </div>
                </div>
            </x-mary-card>

            {{-- Quick Cash Buttons --}}
            <x-mary-card title="Quick Payment">
                <div class="grid grid-cols-2 gap-2">
                    <x-mary-button label="₱500" wire:click="setQuickCash(500)" class="btn-outline btn-sm" />
                    <x-mary-button label="₱1000" wire:click="setQuickCash(1000)" class="btn-outline btn-sm" />
                    <x-mary-button label="₱2000" wire:click="setQuickCash(2000)" class="btn-outline btn-sm" />
                    <x-mary-button label="₱5000" wire:click="setQuickCash(5000)" class="btn-outline btn-sm" />
                </div>
                <div class="mt-4">
                    <x-mary-button label="Exact Cash" wire:click="setExactCash" class="w-full btn-success btn-sm"
                        :disabled="count($cartItems) === 0" />
                </div>
            </x-mary-card>

            {{-- Checkout Button --}}
            <x-mary-button label="Process Payment" wire:click="openPaymentModal" class="w-full btn-primary btn-lg"
                :disabled="count($cartItems) === 0 || !$currentShift" />
        </div>
    </div>

    {{-- Start Shift Modal --}}
    <x-mary-modal wire:model="showStartShiftModal" title="Start Sales Shift"
        subtitle="Initialize your cash drawer and begin sales">
        <div class="space-y-4">
            <x-mary-select label="Warehouse" :options="$warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name])" wire:model="selectedWarehouse"
                placeholder="Select warehouse" option-value="value" option-label="label" />

            <x-mary-input label="Opening Cash Amount" wire:model="openingCash" type="number" step="0.01"
                placeholder="0.00" hint="Enter the cash amount in your drawer to start the shift" />

            <x-mary-textarea label="Opening Notes (Optional)" wire:model="openingNotes"
                placeholder="Any notes about the shift start..." rows="3" />

            <div class="p-4 rounded-lg bg-info/10">
                <div class="flex items-start space-x-2">
                    <x-heroicon-o-information-circle class="w-5 h-5 mt-0.5 text-info" />
                    <div class="text-sm">
                        <p class="font-medium text-info">Shift Requirements:</p>
                        <ul class="mt-1 space-y-1 text-gray-700">
                            <li>• Count your cash drawer carefully before starting</li>
                            <li>• This amount will be used for end-of-shift reconciliation</li>
                            <li>• All sales will be tracked under this shift</li>
                            <li>• You cannot process sales without an active shift</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showStartShiftModal', false)" />
            <x-mary-button label="Start Shift" wire:click="startShift" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Payment Modal --}}
    <x-mary-modal wire:model="showPaymentModal" title="Process Payment" subtitle="Complete the sale transaction">
        <div class="space-y-4">
            {{-- Order Summary --}}
            <div class="p-4 rounded-lg bg-base-200">
                <h4 class="mb-3 font-semibold">Order Summary</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>₱{{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if ($discountAmount > 0)
                        <div class="flex justify-between text-success">
                            <span>Discount:</span>
                            <span>-₱{{ number_format($discountAmount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span>Tax:</span>
                        <span>₱{{ number_format($taxAmount, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 text-lg font-bold border-t">
                        <span>Total:</span>
                        <span>₱{{ number_format($totalAmount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Method --}}
            <x-mary-select label="Payment Method" :options="[
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'card', 'label' => 'Credit/Debit Card'],
                ['value' => 'gcash', 'label' => 'GCash'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
            ]" wire:model.live="paymentMethod"
                option-label="label" option-value="value" />

            {{-- Payment Amount --}}
            <div class="space-y-2">
                <x-mary-input label="Amount Received" wire:model.live="paidAmount" type="number" step="0.01" />

                @if ($paymentMethod === 'cash')
                    <div class="flex gap-2">
                        <x-mary-button label="Exact Amount" wire:click="setExactCash" class="btn-outline btn-sm" />
                    </div>
                @endif
            </div>

            {{-- Change Calculation --}}
            @if ($changeAmount > 0)
                <div class="p-3 border rounded-lg bg-success/10 border-success/20">
                    <div class="text-center">
                        <div class="text-lg font-bold text-success">
                            Change: ₱{{ number_format($changeAmount, 2) }}
                        </div>
                    </div>
                </div>
            @endif

            {{-- Sale Notes --}}
            <x-mary-textarea label="Sale Notes (Optional)" wire:model="saleNotes"
                placeholder="Any additional notes..." rows="2" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showPaymentModal', false)" />
            <x-mary-button label="Complete Sale" wire:click="completeSale" class="btn-success" :disabled="$paidAmount < $totalAmount" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Barcode Scanner Modal --}}
    <x-mary-modal wire:model="showBarcodeModal" title="Barcode Scanner"
        subtitle="Scan multiple items then add to cart">
        <div class="space-y-4">
            {{-- Barcode Input --}}
            <div class="p-4 rounded-lg bg-primary/10">
                <x-mary-input label="Barcode Scanner" wire:model.live="barcodeInput"
                    placeholder="Scan barcode here..." hint="Products will be added automatically as you scan" />
                <div class="flex gap-2 mt-2">
                    <x-mary-button label="Clear" wire:click="clearBarcodeInput" class="btn-ghost btn-sm" />
                    <x-mary-button label="Process" wire:click="processBarcodeInput" class="btn-primary btn-sm" />
                </div>
            </div>

            {{-- Scanned Items --}}
            @if (count($scannedItems) > 0)
                <div>
                    <h4 class="mb-3 font-semibold">Scanned Items ({{ count($scannedItems) }})</h4>
                    <div class="space-y-2 overflow-y-auto max-h-64">
                        @foreach ($scannedItems as $index => $item)
                            <div class="flex items-center gap-3 p-3 border rounded-lg bg-base-200">
                                <div class="flex-1">
                                    <div class="font-medium">{{ $item['name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $item['sku'] }} • Stock:
                                        {{ $item['available_stock'] }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    {{-- Quantity Controls --}}
                                    <div class="flex items-center gap-1">
                                        <x-mary-button icon="o-minus"
                                            wire:click="updateScannedItemQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                            class="btn-ghost btn-xs" />
                                        <span
                                            class="font-semibold min-w-[2rem] text-center text-sm">{{ $item['quantity'] }}</span>
                                        <x-mary-button icon="o-plus"
                                            wire:click="updateScannedItemQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                            class="btn-ghost btn-xs" />
                                    </div>

                                    {{-- Price --}}
                                    <div class="text-right min-w-[4rem]">
                                        <div class="text-sm font-semibold">₱{{ number_format($item['subtotal'], 2) }}
                                        </div>
                                    </div>

                                    {{-- Remove Button --}}
                                    <x-mary-button icon="o-x-mark"
                                        wire:click="removeScannedItem({{ $index }})"
                                        class="btn-ghost btn-xs text-error" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Batch Total --}}
                    <div class="pt-2 mt-3 border-t border-gray-300">
                        <div class="flex justify-between text-sm font-semibold">
                            <span>Batch Total:</span>
                            <span>₱{{ number_format(collect($scannedItems)->sum('subtotal'), 2) }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <x-mary-button label="Clear All" wire:click="clearScannedItems" class="btn-ghost btn-sm" />
                        <x-mary-button label="Add to Cart" wire:click="addScannedItemsToCart"
                            class="flex-1 btn-primary" />
                    </div>
                </div>
            @else
                <div class="py-8 text-center">
                    <x-heroicon-o-qr-code class="w-12 h-12 mx-auto text-gray-400" />
                    <p class="mt-2 text-gray-500">No items scanned yet</p>
                    <p class="text-sm text-gray-400">Scan barcodes to add products</p>
                </div>
            @endif

            {{-- Current Cart Summary --}}
            <div class="p-3 border rounded-lg bg-primary/5 border-primary/20">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-primary-700">Current Cart</span>
                    <span class="text-xs text-primary-600">{{ count($cartItems) }} items</span>
                </div>
                @if (count($cartItems) > 0)
                    <div class="space-y-1 overflow-y-auto max-h-20">
                        @foreach (array_slice($cartItems, -3, 3, true) as $key => $item)
                            <div class="flex justify-between text-xs text-primary-700">
                                <span class="truncate">{{ $item['name'] }}</span>
                                <span>{{ $item['quantity'] }}x ₱{{ number_format($item['price'], 2) }}</span>
                            </div>
                        @endforeach
                        @if (count($cartItems) > 3)
                            <div class="text-xs text-center text-primary-600">... and {{ count($cartItems) - 3 }} more
                                items</div>
                        @endif
                    </div>
                    <div class="pt-2 mt-2 border-t border-primary/20">
                        <div class="flex justify-between text-sm font-semibold text-primary-800">
                            <span>Cart Total:</span>
                            <span>₱{{ number_format($totalAmount, 2) }}</span>
                        </div>
                    </div>
                @else
                    <div class="py-2 text-xs text-center text-primary-600">Cart is empty</div>
                @endif
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showBarcodeModal', false)" class="btn-ghost" />
            <x-mary-button label="Clear Input" wire:click="clearBarcodeInput" class="btn-outline" />
            <x-mary-button label="Add to Cart ({{ count($scannedItems) }})" wire:click="addScannedItemsToCart"
                class="btn-primary" :disabled="count($scannedItems) === 0" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Customer Search Modal --}}
    <x-mary-modal wire:model="showSearchCustomerModal" title="Search Customer" subtitle="Find existing customer">
        <div class="space-y-4">
            <x-mary-input label="Search" wire:model.live.debounce="customerSearch"
                placeholder="Search by name, email, or phone..." icon="o-magnifying-glass" />

            @if (count($customerSearchResults) > 0)
                <div class="space-y-2 overflow-y-auto max-h-64">
                    @foreach ($customerSearchResults as $customer)
                        <div class="p-3 transition-colors border rounded-lg cursor-pointer hover:bg-primary/10 hover:border-primary"
                            wire:click="selectSearchedCustomer({{ $customer['id'] }})">
                            <div>
                                <div class="font-medium">{{ $customer['name'] }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ $customer['email'] ?? 'No email' }} • {{ $customer['phone'] ?? 'No phone' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif (strlen($customerSearch) >= 2)
                <div class="py-8 text-center">
                    <x-heroicon-o-user-minus class="w-12 h-12 mx-auto text-gray-400" />
                    <p class="mt-2 text-gray-500">No customers found</p>
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showSearchCustomerModal', false)" />
            <x-mary-button label="Create New Customer" wire:click="openCustomerModal" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- New Customer Modal --}}
    <x-mary-modal wire:model="showCustomerModal" title="Create New Customer" subtitle="Add customer information">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-mary-input label="Full Name" wire:model="customerName" placeholder="Customer name"
                class="md:col-span-2" />
            <x-mary-input label="Email" wire:model="customerEmail" placeholder="customer@example.com" />
            <x-mary-input label="Phone" wire:model="customerPhone" placeholder="Phone number" />
            <x-mary-textarea label="Address" wire:model="customerAddress" placeholder="Customer address"
                rows="2" class="md:col-span-2" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showCustomerModal', false)" />
            <x-mary-button label="Create Customer" wire:click="createCustomer" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Discount Modal --}}
    <x-mary-modal wire:model="showDiscountModal" title="Apply Discount" subtitle="Add discount to the order">
        <div class="space-y-4">
            <x-mary-select label="Discount Type" :options="[
                ['value' => 'percentage', 'label' => 'Percentage (%)'],
                ['value' => 'fixed', 'label' => 'Fixed Amount (₱)'],
            ]" wire:model.live="discountType" />

            <x-mary-input label="Discount Value" wire:model="discountValue" type="number" step="0.01"
                placeholder="{{ $discountType === 'percentage' ? 'Enter percentage (e.g., 10)' : 'Enter amount (e.g., 100.00)' }}" />

            @if ($discountValue && is_numeric($discountValue))
                <div class="p-3 rounded-lg bg-info/10">
                    <div class="text-center">
                        <div class="text-lg font-bold text-info">
                            Preview:
                            -₱{{ number_format($discountType === 'percentage' ? $subtotal * ($discountValue / 100) : min($discountValue, $subtotal), 2) }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showDiscountModal', false)" />
            <x-mary-button label="Apply Discount" wire:click="applyDiscount" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Hold Sale Modal --}}
    <x-mary-modal wire:model="showHoldSaleModal" title="Hold Sale" subtitle="Save current sale for later">
        <div class="space-y-4">
            <x-mary-input label="Reference Name" wire:model="holdReference" placeholder="Hold reference" />
            <x-mary-textarea label="Notes" wire:model="holdNotes"
                placeholder="Optional notes about this held sale..." rows="3" />

            <div class="p-4 rounded-lg bg-warning/10">
                <div class="flex items-start space-x-2">
                    <x-heroicon-o-information-circle class="w-5 h-5 mt-0.5 text-warning" />
                    <div class="text-sm">
                        <p class="font-medium text-warning">Hold Sale Information:</p>
                        <ul class="mt-1 space-y-1 text-gray-700">
                            <li>• Current cart will be saved and cleared</li>
                            <li>• You can retrieve this sale later from "Held Sales"</li>
                            <li>• Customer and discount information will be preserved</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showHoldSaleModal', false)" />
            <x-mary-button label="Hold Sale" wire:click="holdSale" class="btn-warning" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Held Sales Modal --}}
    <x-mary-modal wire:model="showHeldSalesModal" title="Held Sales" subtitle="Retrieve previously held sales"
        box-class="w-11/12 max-w-4xl">
        @if (count($heldSales) > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Date/Time</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($heldSales as $heldSale)
                            <tr>
                                <td class="font-medium">{{ $heldSale['invoice_number'] }}</td>
                                <td>{{ $heldSale['customer_name'] }}</td>
                                <td>{{ $heldSale['items_count'] }} items</td>
                                <td class="font-bold">₱{{ number_format($heldSale['total_amount'], 2) }}</td>
                                <td class="text-sm">{{ $heldSale['created_at'] }}</td>
                                <td class="text-sm">
                                    @if ($heldSale['notes'])
                                        {{ str_replace('HELD SALE: ', '', $heldSale['notes']) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-1">
                                        <x-mary-button label="Retrieve"
                                            wire:click="retrieveHeldSale({{ $heldSale['id'] }})"
                                            class="btn-primary btn-xs" />
                                        <x-mary-button icon="o-trash"
                                            wire:click="deleteHeldSale({{ $heldSale['id'] }})"
                                            wire:confirm="Delete this held sale?"
                                            class="btn-ghost btn-xs text-error" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center">
                <x-heroicon-o-document-text class="w-12 h-12 mx-auto text-gray-400" />
                <p class="mt-2 text-gray-500">No held sales found</p>
                <p class="text-sm text-gray-400">Hold a sale to see it here</p>
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Close" wire:click="$set('showHeldSalesModal', false)" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Receipt Print Modal --}}
    <x-mary-modal wire:model="showReceiptModal" title="Sale Completed" subtitle="Transaction processed successfully">
        <div class="space-y-4">
            <div class="p-4 border rounded-lg bg-success/10 border-success/20">
                <div class="text-center">
                    <x-heroicon-o-check-circle class="w-16 h-16 mx-auto mb-2 text-success" />
                    <h3 class="text-lg font-bold text-success">Sale Completed Successfully!</h3>
                    <p class="mt-1 text-sm text-success-700">Invoice #{{ $lastInvoiceNumber ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="p-4 rounded-lg bg-base-200">
                <h4 class="mb-2 font-semibold">Transaction Summary</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span>Payment Method:</span>
                        <span class="capitalize">{{ $lastPaymentMethod ?? 'Cash' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Amount Paid:</span>
                        <span>₱{{ number_format($lastPaidAmount ?? 0, 2) }}</span>
                    </div>
                    @if (($lastChangeAmount ?? 0) > 0)
                        <div class="flex justify-between font-medium text-success">
                            <span>Change Given:</span>
                            <span>₱{{ number_format($lastChangeAmount ?? 0, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-4 rounded-lg bg-info/10">
                <div class="flex items-start space-x-2">
                    <x-heroicon-o-information-circle class="w-5 h-5 mt-0.5 text-info" />
                    <div class="text-sm">
                        <p class="font-medium text-info">Important Reminder:</p>
                        <p class="mt-1 text-gray-700">
                            This is a provisional receipt for internal tracking only.
                            Please issue an official BIR receipt manually for legal compliance.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Print Receipt" wire:click="printReceipt" class="btn-primary" />
            <x-mary-button label="New Sale" wire:click="startNewSale" class="btn-success" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Barcode Input Focus Script --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('barcode-modal-opened', () => {
                setTimeout(() => {
                    const barcodeInput = document.querySelector(
                        'input[wire\\:model\\.live="barcodeInput"]');
                    if (barcodeInput) {
                        barcodeInput.focus();
                    }
                }, 100);
            });

            // Auto-focus barcode input when modal opens
            Livewire.on('focus-barcode-input', () => {
                setTimeout(() => {
                    const input = document.getElementById('barcode-input') ||
                        document.querySelector('input[wire\\:model\\.live="barcodeInput"]');
                    if (input) {
                        input.focus();
                        input.select();
                    }
                }, 100);
            });

            // Handle keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // F1 - Open barcode scanner
                if (e.key === 'F1') {
                    e.preventDefault();
                    Livewire.dispatch('open-barcode-modal');
                }

                // F2 - Open payment modal (if cart has items)
                if (e.key === 'F2') {
                    e.preventDefault();
                    Livewire.dispatch('open-payment-modal');
                }

                // F3 - Clear cart
                if (e.key === 'F3') {
                    e.preventDefault();
                    if (confirm('Clear all items from cart?')) {
                        Livewire.dispatch('clear-cart');
                    }
                }

                // F4 - Hold sale
                if (e.key === 'F4') {
                    e.preventDefault();
                    Livewire.dispatch('open-hold-sale-modal');
                }

                // F5 - View held sales
                if (e.key === 'F5') {
                    e.preventDefault();
                    Livewire.dispatch('open-held-sales-modal');
                }

                // Escape - Close any open modal
                if (e.key === 'Escape') {
                    Livewire.dispatch('close-all-modals');
                }
            });

            // Auto-submit barcode when Enter is pressed
            document.addEventListener('keypress', function(e) {
                if (e.target && e.target.getAttribute('wire:model.live') === 'barcodeInput') {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        Livewire.dispatch('process-barcode');
                    }
                }
            });
        });

        // Print function for receipts
        function printReceipt() {
            window.print();
        }

        // Notification sound for successful operations
        function playSuccessSound() {
            // Create a simple beep sound
            const audioContext = new(window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        }

        // Listen for Livewire events to play sounds
        document.addEventListener('livewire:init', () => {
            Livewire.on('sale-completed', () => {
                playSuccessSound();
            });

            Livewire.on('item-added-to-cart', () => {
                // Shorter beep for item additions
                const audioContext = new(window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 600;
                oscillator.type = 'sine';

                gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.05);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.05);
            });
        });
    </script>

    {{-- Print Styles for Receipt --}}
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            .receipt-content,
            .receipt-content * {
                visibility: visible;
            }

            .receipt-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Hide all buttons and UI elements when printing */
            .btn,
            button,
            .modal,
            .navbar {
                display: none !important;
            }
        }

        /* Receipt styling */
        .receipt-content {
            font-family: 'Courier New', monospace;
            max-width: 300px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            background: white;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .receipt-items {
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .receipt-total {
            text-align: right;
            font-weight: bold;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
        }
    </style>
</div>
