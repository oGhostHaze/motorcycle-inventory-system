<div class="min-h-screen bg-base-200 hide-all-scrollbarss">
    {{-- Header with Shift Status --}}
    <div class="p-4 shadow-sm bg-base-100">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                @if ($currentShift)
                    <div class="flex items-center space-x-2">
                        <x-mary-badge value="Shift: {{ $currentShift->shift_number }}" class="badge-success" />
                        <span class="text-sm text-base-600">
                            Started: {{ $currentShift->started_at->format('H:i') }}
                        </span>
                    </div>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                @if ($currentShift)
                    <div class="text-right">
                        <div class="text-sm font-medium">{{ $currentShift->total_transactions }} transactions</div>
                        <div class="text-sm text-base-600">₱{{ number_format($currentShift->total_sales, 2) }} total
                        </div>
                    </div>
                @endif
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

                    {{-- Product Search Results --}}
                    @if (count($searchResults) > 0)
                        <div class="mt-4">
                            <h5 class="mb-2 font-medium text-base-700">Search Results:</h5>
                            <div class="overflow-y-auto border rounded-lg shadow-sm bg-base h-60">
                                <div class="space-y-0">
                                    @foreach ($searchResults as $product)
                                        <div
                                            class="flex items-center justify-between p-3 transition-colors border-b last:border-b-0 hover:bg-base-50">
                                            <div class="flex-1 min-w-0">
                                                <div class="font-medium truncate">{{ $product['name'] }}</div>
                                                <div class="text-sm truncate text-base-500">{{ $product['sku'] }} |
                                                    ₱{{ number_format($product['selling_price'], 2) }}</div>
                                                @php
                                                    $stock = $product['inventory'][0]['quantity_available'] ?? 0;
                                                @endphp
                                                <div
                                                    class="text-sm {{ $stock <= 0 ? 'text-red-500 font-semibold' : 'text-base-500' }}">
                                                    Stock: {{ $stock }}
                                                    @if ($stock <= 0)
                                                        <span class="ml-1 text-xs">(Out of Stock)</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex flex-shrink-0 gap-2">
                                                <x-mary-button icon="o-plus"
                                                    wire:click="addToCart({{ $product['id'] }})"
                                                    class="btn-xs btn-primary" :disabled="!$currentShift || $stock <= 0"
                                                    title="Add with default price" />
                                                <x-mary-button icon="o-currency-dollar"
                                                    wire:click="addToCartWithPriceSelection({{ $product['id'] }})"
                                                    class="btn-xs btn-secondary" :disabled="!$currentShift || $stock <= 0"
                                                    title="Add with price selection" />
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="mt-4">
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <x-mary-input wire:model.live.debounce.300ms="searchService"
                                placeholder="Search services (labor, diagnostics, etc.)" icon="o-wrench-screwdriver"
                                class="w-full" />
                        </div>
                        <x-mary-button wire:click="openServiceModal" icon="o-plus" class="btn-primary"
                            tooltip="Browse Services" />
                    </div>

                    {{-- Service Search Results --}}
                    @if (!empty($serviceResults))
                        <div class="mt-2 border rounded-lg shadow-sm bg-base-100 border-base-300">
                            @foreach ($serviceResults as $service)
                                <div class="flex items-center justify-between p-3 border-b last:border-b-0 border-base-200 hover:bg-base-50"
                                    wire:click="addServiceToCart({{ $service->id }})" role="button">
                                    <div class="flex-1">
                                        <div class="font-medium">{{ $service->name }}</div>
                                        <div class="text-sm text-base-content/60">
                                            {{ $service->code }} • {{ $service->service_type }} •
                                            {{ $service->formatted_duration }}
                                        </div>
                                        @if ($service->description)
                                            <div class="text-xs text-base-content/50">
                                                {{ Str::limit($service->description, 50) }}</div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-primary">{{ $service->formatted_price }}</div>
                                        @if ($service->requires_parts)
                                            <div class="text-xs badge badge-warning">Requires Parts</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-mary-card>
            {{-- Shopping Cart --}}
            <x-mary-card class="{{ !$currentShift ? 'opacity-50' : '' }}">
                {{-- Shopping Cart Header with Bulk Actions --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Shopping Cart ({{ count($cartItems) }} items)</h3>
                    @if (count($cartItems) > 0)
                        <x-mary-button icon="o-currency-dollar" wire:click="openBulkPriceSelection"
                            class="btn-xs btn-outline" :disabled="!$currentShift" label="Bulk Price" />
                    @endif
                </div>{{-- Add this before the cart items section --}}
                @if ($this->hasSerialTrackingItems() && !$selectedCustomer)
                    <div class="p-3 mb-4 border rounded-lg bg-warning/10 border-warning/20">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-warning" />
                            <div class="text-sm">
                                <p class="font-medium text-warning">Customer Required</p>
                                <p class="text-warning/80">This cart contains items that require serial number tracking.
                                    Please select a customer to continue.</p>
                            </div>
                        </div>
                    </div>
                @endif
                @if (count($cartItems) > 0)
                    <div class="space-y-3">
                        @foreach ($cartItems as $cartKey => $item)
                            <div class="flex items-center gap-4 p-3 border rounded-lg bg-base-50">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        {{-- Item Type Icon --}}
                                        @if ($item['item_type'] === 'service')
                                            <x-mary-icon name="o-wrench-screwdriver" class="w-4 h-4 text-primary" />
                                        @else
                                            <x-mary-icon name="o-cube" class="w-4 h-4 text-secondary" />
                                        @endif
                                        <div class="font-medium">{{ $item['name'] }}</div>
                                    </div>
                                    <div class="text-sm text-base-500">{{ $item['code'] }}</div>

                                    {{-- Stock Info for Products Only --}}
                                    @if ($item['item_type'] === 'product' && isset($item['available_stock']))
                                        <div class="text-xs text-base-400">Stock: {{ $item['available_stock'] }}</div>
                                    @endif
                                </div>

                                {{-- Serial Number Status (Products Only) --}}
                                @if ($item['item_type'] === 'product' && isset($item['track_serial']) && $item['track_serial'])
                                    <div class="mt-1 text-xs">
                                        @php
                                            $serialCount = count($item['serial_numbers'] ?? []);
                                            $required = $item['quantity'];
                                        @endphp
                                        @if ($serialCount === $required)
                                            <span class="text-success">✓ {{ $serialCount }}/{{ $required }}
                                                serials</span>
                                        @elseif(!$selectedCustomer)
                                            <span class="text-error">⚠ Customer required for serials</span>
                                        @else
                                            <span class="text-warning">⚠ {{ $serialCount }}/{{ $required }}
                                                serials</span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Quantity Controls --}}
                                <div class="flex items-center gap-2">
                                    <x-mary-button icon="o-minus"
                                        wire:click="decreaseQuantity('{{ $cartKey }}')" class="btn-xs btn-ghost"
                                        :disabled="!$currentShift" />
                                    <x-mary-input wire:model.blur="cartItems.{{ $cartKey }}.quantity"
                                        wire:change="updateCartItemQuantity('{{ $cartKey }}', $event.target.value)"
                                        class="w-16 text-center input-xs" :disabled="!$currentShift" />
                                    <x-mary-button icon="o-plus"
                                        wire:click="increaseQuantity('{{ $cartKey }}')" class="btn-xs btn-ghost"
                                        :disabled="!$currentShift || ($item['item_type'] === 'product' && $item['quantity'] >= ($item['available_stock'] ?? 0))" />
                                </div>

                                {{-- Serial Button (Products Only) --}}
                                @if ($item['item_type'] === 'product' && isset($item['track_serial']) && $item['track_serial'])
                                    @if ($selectedCustomer)
                                        <x-mary-button icon="o-qr-code"
                                            wire:click="openSerialModal('{{ $cartKey }}')"
                                            class="btn-xs {{ count($item['serial_numbers'] ?? []) === $item['quantity'] ? 'btn-success' : 'btn-warning' }}"
                                            :disabled="!$currentShift" title="Enter Serial Numbers" />
                                    @else
                                        <x-mary-button icon="o-qr-code" class="btn-xs btn-error btn-disabled" disabled
                                            title="Select customer first to enter serial numbers" />
                                    @endif
                                @endif

                                {{-- Price with selection button (Products Only) --}}
                                @if ($item['item_type'] === 'product')
                                    <div class="w-32">
                                        <div class="flex items-center gap-1">
                                            <x-mary-input wire:model.blur="cartItems.{{ $cartKey }}.price"
                                                wire:change="updatePrice('{{ $cartKey }}', $event.target.value)"
                                                class="text-right input-xs" :disabled="!$currentShift" />
                                            <x-mary-button icon="o-ellipsis-vertical"
                                                wire:click="openPriceSelection('{{ $cartKey }}')"
                                                class="btn-xs btn-ghost" :disabled="!$currentShift" title="Select price" />
                                        </div>
                                    </div>
                                @else
                                    {{-- Services have fixed price --}}
                                    <div class="w-32">
                                        <div class="text-sm text-right text-base-500">
                                            ₱{{ number_format($item['price'], 2) }}
                                        </div>
                                    </div>
                                @endif

                                {{-- Subtotal --}}
                                <div class="w-20 font-bold text-right">
                                    ₱{{ number_format($item['subtotal'], 2) }}
                                </div>

                                {{-- Remove Button --}}
                                <x-mary-button icon="o-trash" wire:click="removeFromCart('{{ $cartKey }}')"
                                    class="btn-xs btn-ghost text-error" :disabled="!$currentShift" />
                            </div>
                        @endforeach

                        {{-- Cart Actions --}}
                        <div class="flex gap-2 pt-4 border-t">
                            <x-mary-button label="Clear Cart" wire:click="clearCart" class="btn-ghost btn-sm"
                                :disabled="!$currentShift" />
                            <x-mary-button label="Hold Sale" wire:click="openHoldSaleModal"
                                class="btn-warning btn-sm" :disabled="!$currentShift" />
                            <x-mary-button label="Held Sales" wire:click="openHeldSalesModal" class="btn-info btn-sm"
                                :disabled="!$currentShift" />
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-shopping-cart class="w-12 h-12 mx-auto text-base-400" />
                        <p class="mt-2 text-base-500">Cart is empty</p>
                        <p class="text-sm text-base-400">
                            {{ $currentShift ? 'Search for products or services to add to cart' : 'Start a shift to begin adding items' }}
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
                @if ($this->hasSerialTrackingItems())
                    <div class="p-2 mb-3 text-xs border rounded bg-info/10 border-info/20 text-info">
                        <x-heroicon-o-information-circle class="inline w-4 h-4 mr-1" />
                        Customer selection required for serial tracking
                    </div>
                @endif

                @if ($selectedCustomer)
                    @php $customer = \App\Models\Customer::find($selectedCustomer) @endphp
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium">{{ $customer->name }}</div>
                                @if ($customer->email)
                                    <div class="text-sm text-base-500">{{ $customer->email }}</div>
                                @endif
                                @if ($customer->phone)
                                    <div class="text-sm text-base-500">{{ $customer->phone }}</div>
                                @endif
                            </div>
                            <x-mary-button icon="o-x-mark" wire:click="$set('selectedCustomer', null)"
                                class="btn-xs btn-ghost" :disabled="!$currentShift" />
                        </div>
                    </div>
                @else
                    <div class="space-y-2">
                        <x-mary-button label="Search Customer" wire:click="openSearchCustomerModal"
                            class="w-full btn-outline" :disabled="!$currentShift" />
                        <x-mary-button label="New Customer" wire:click="openCustomerModal" class="w-full btn-primary"
                            :disabled="!$currentShift" />
                        <x-mary-button label="Walk-in Customer" wire:click="selectWalkInCustomer"
                            class="w-full btn-ghost" :disabled="!$currentShift || $this->hasSerialTrackingItems()" />
                    </div>
                @endif
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

            {{-- Checkout Button --}}
            @php
                $canCheckout = count($cartItems) > 0 && $currentShift && $this->validateCustomerForSerials();
            @endphp

            <x-mary-button label="Process Payment" wire:click="openPaymentModal" class="w-full btn-primary btn-lg"
                :disabled="!$canCheckout" />

            @if (!$this->validateCustomerForSerials())
                <p class="mt-1 text-xs text-center text-error">
                    Customer required for serial tracking items
                </p>
            @endif
        </div>
    </div>

    {{-- Individual Price Selection Modal --}}
    <x-mary-modal wire:model="showPriceModal" title="Select Price" class="backdrop-blur">
        <div class="space-y-3">
            @if ($availablePrices)
                @foreach ($availablePrices as $priceType => $priceData)
                    <div class="flex items-center justify-between p-3 border rounded-lg cursor-pointer hover:bg-base-50"
                        wire:click="selectPrice('{{ $priceType }}')">
                        <span class="font-medium">{{ $priceData['label'] }}</span>
                        <span
                            class="text-lg font-bold text-primary">₱{{ number_format($priceData['value'], 2) }}</span>
                    </div>
                @endforeach
            @endif
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showPriceModal', false)" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Add to Cart Price Selection Modal --}}
    <x-mary-modal wire:model="showAddPriceModal" title="Select Price for Adding to Cart" class="backdrop-blur">
        <div class="space-y-3">
            @if ($availablePrices)
                @foreach ($availablePrices as $priceType => $priceData)
                    <div class="flex items-center justify-between p-3 border rounded-lg cursor-pointer hover:bg-base-50"
                        wire:click="addToCartWithPrice('{{ $priceType }}')">
                        <span class="font-medium">{{ $priceData['label'] }}</span>
                        <span
                            class="text-lg font-bold text-primary">₱{{ number_format($priceData['value'], 2) }}</span>
                    </div>
                @endforeach
            @endif
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showAddPriceModal', false)" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Bulk Price Selection Modal --}}
    <x-mary-modal wire:model="showBulkPriceModal" title="Apply Bulk Price Change" class="backdrop-blur">
        <div class="space-y-4">
            <p class="text-base-600">Select a price type to apply to all compatible items in the cart:</p>

            <x-mary-radio wire:model="bulkPriceType" :options="[
                ['id' => 'selling_price', 'name' => 'Selling Price (Default)'],
                ['id' => 'wholesale_price', 'name' => 'Wholesale Price'],
                ['id' => 'alt_price1', 'name' => 'Alternative Price 1'],
                ['id' => 'alt_price2', 'name' => 'Alternative Price 2'],
                ['id' => 'alt_price3', 'name' => 'Alternative Price 3'],
            ]" option-value="id" option-label="name" />

            <div class="p-3 border border-yellow-200 rounded-lg bg-yellow-50">
                <p class="text-sm text-yellow-800">
                    <strong>Note:</strong> Only products that have the selected price type available will be updated.
                </p>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showBulkPriceModal', false)" />
            <x-mary-button label="Apply to All" wire:click="applyBulkPrice" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

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
                        <ul class="mt-1 space-y-1 text-base-700">
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
    {{-- Enhanced Payment Modal with Additional Change Features --}}
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
                <x-mary-input label="Amount Received" wire:model.live="paidAmount" type="number" step="0.01"
                    class="{{ $paidAmount < $totalAmount ? 'input-error' : ($changeAmount > 0 ? 'input-success' : '') }}" />

                @if ($paymentMethod === 'cash')
                    <div class="flex flex-wrap gap-2">
                        <x-mary-button label="Exact Amount" wire:click="setExactCash" class="btn-outline btn-sm" />
                        {{-- Quick Cash Buttons --}}
                        <x-mary-button label="₱{{ number_format(ceil($totalAmount / 100) * 100, 0) }}"
                            wire:click="setQuickCash({{ ceil($totalAmount / 100) * 100 }})"
                            class="btn-outline btn-sm" />
                        <x-mary-button label="₱{{ number_format(ceil($totalAmount / 500) * 500, 0) }}"
                            wire:click="setQuickCash({{ ceil($totalAmount / 500) * 500 }})"
                            class="btn-outline btn-sm" />
                        <x-mary-button label="₱{{ number_format(ceil($totalAmount / 1000) * 1000, 0) }}"
                            wire:click="setQuickCash({{ ceil($totalAmount / 1000) * 1000 }})"
                            class="btn-outline btn-sm" />
                    </div>
                @endif

                {{-- Payment Status Indicator --}}
                @if ($paidAmount > 0)
                    <div class="text-sm">
                        @if ($paidAmount < $totalAmount)
                            <div class="text-error">
                                ⚠️ Insufficient payment: ₱{{ number_format($totalAmount - $paidAmount, 2) }} remaining
                            </div>
                        @elseif ($paidAmount == $totalAmount)
                            <div class="text-success">
                                ✅ Exact payment received
                            </div>
                        @else
                            <div class="text-info">
                                💰 Overpayment: ₱{{ number_format($changeAmount, 2) }} change due
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Enhanced Change Calculation --}}
            @if ($changeAmount > 0)
                <div class="p-4 border rounded-lg bg-success/10 border-success/20">
                    <div class="text-center">
                        <div class="mb-2 text-2xl font-bold text-success">
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
        subtitle="Scan multiple items then add to cart" box-class="w-11/12 max-w-4xl">
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
                                    <div class="text-sm text-base-500">{{ $item['sku'] }} • Stock:
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
                    <div class="pt-2 mt-3 border-t border-base-300">
                        <div class="flex justify-between text-sm font-semibold">
                            <span>Batch Total:</span>
                            <span>₱{{ number_format(collect($scannedItems)->sum('subtotal'), 2) }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="py-8 text-center">
                    <x-heroicon-o-qr-code class="w-12 h-12 mx-auto text-base-400" />
                    <p class="mt-2 text-base-500">No items scanned yet</p>
                    <p class="text-sm text-base-400">Scan barcodes to add products</p>
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
            <x-mary-button label="Clear All" wire:click="clearScannedItems" class="btn-outline" />
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
                                <div class="text-sm text-base-500">
                                    {{ $customer['email'] ?? 'No email' }} • {{ $customer['phone'] ?? 'No phone' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif (strlen($customerSearch) >= 2)
                <div class="py-8 text-center">
                    <x-heroicon-o-user-minus class="w-12 h-12 mx-auto text-base-400" />
                    <p class="mt-2 text-base-500">No customers found</p>
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

    {{-- Enhanced Discount Modal (your existing structure with small additions) --}}
    <x-mary-modal wire:model="showDiscountModal" title="Apply Discount" subtitle="Add discount to the order">
        <div class="space-y-4">
            {{-- Show current subtotal --}}
            <div class="p-3 rounded-lg bg-base-200">
                <div class="flex justify-between text-sm">
                    <span>Current Subtotal:</span>
                    <span class="font-semibold">₱{{ number_format($subtotal, 2) }}</span>
                </div>
            </div>

            {{-- Show current discount if any --}}
            @if ($discountAmount > 0)
                <div class="p-3 border rounded-lg bg-warning/10 border-warning/20">
                    <div class="text-sm">
                        <div class="flex justify-between">
                            <span>Current Discount:</span>
                            <span class="font-semibold">
                                {{ $discountType === 'percentage' ? $discountValue . '%' : '₱' . number_format($discountValue, 2) }}
                                = -₱{{ number_format($discountAmount, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <x-mary-select label="Discount Type" :options="[
                ['id' => 'percentage', 'name' => 'Percentage (%)'],
                ['id' => 'fixed', 'name' => 'Fixed Amount (₱)'],
            ]" wire:model.live="discountType" />

            <x-mary-input label="Discount Value" wire:model.live="discountValue" type="number" step="0.01"
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

                {{-- Validation warnings --}}
                @if ($discountType === 'percentage' && $discountValue > 100)
                    <div class="text-sm text-error">⚠️ Percentage cannot exceed 100%</div>
                @endif
                @if ($discountType === 'fixed' && $discountValue > $subtotal)
                    <div class="text-sm text-warning">⚠️ Fixed discount will be limited to subtotal amount</div>
                @endif
            @endif
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showDiscountModal', false)" />
            @if ($discountAmount > 0)
                <x-mary-button label="Remove Current" wire:click="removeDiscount" class="btn-outline btn-warning" />
            @endif
            <x-mary-button label="Apply Discount" wire:click="applyDiscount" class="btn-primary"
                :disabled="$discountType === 'percentage' && $discountValue > 100" />
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
                        <ul class="mt-1 space-y-1 text-base-700">
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
                <x-heroicon-o-document-text class="w-12 h-12 mx-auto text-base-400" />
                <p class="mt-2 text-base-500">No held sales found</p>
                <p class="text-sm text-base-400">Hold a sale to see it here</p>
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
                        <p class="mt-1 text-base-700">
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

    {{-- Serial Number Entry Modal --}}
    <x-mary-modal wire:model="showSerialModal" title="Enter Serial Numbers" persistent box-class="w-11/12 max-w-2xl">
        <div class="space-y-4">
            <div class="text-sm text-gray-600">
                Enter {{ $requiredSerials }} serial number(s) for this product.
                Current: {{ count($enteredSerials) }}/{{ $requiredSerials }}
            </div>

            {{-- Serial input --}}
            <div class="flex gap-2">
                <x-mary-input wire:model="serialInput" placeholder="Scan or enter serial number"
                    wire:keydown.enter="addSerialNumber" class="flex-1"
                    hint="Enter any serial number - will be created if doesn't exist" />
                <x-mary-button label="Add" wire:click="addSerialNumber" class="btn-primary" />
            </div>

            {{-- Bulk entry section --}}
            <div class="pt-4 border-t">
                <h4 class="mb-2 text-sm font-medium">Bulk Entry (Optional)</h4>
                <x-mary-textarea wire:model="bulkSerialInput"
                    placeholder="Enter multiple serial numbers, one per line&#10;SN001&#10;SN002&#10;SN003"
                    rows="3" class="text-sm" />
                <div class="flex gap-2 mt-2">
                    <x-mary-button label="Add All" wire:click="addBulkSerials" class="btn-sm btn-outline" />
                    <span class="self-center text-xs text-gray-500">
                        Remaining: {{ $requiredSerials - count($enteredSerials) }}
                    </span>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="flex gap-2">
                @if (count($enteredSerials) < $requiredSerials)
                    <x-mary-button label="Auto Generate" wire:click="generateSerialNumbers"
                        class="btn-sm btn-outline" />
                @endif
                @if (count($enteredSerials) > 0)
                    <x-mary-button label="Clear All" wire:click="$set('enteredSerials', [])"
                        class="btn-sm btn-ghost" />
                @endif
            </div>

            {{-- Entered serials list --}}
            @if (count($enteredSerials) > 0)
                <div class="space-y-2">
                    <h4 class="font-medium">Entered Serial Numbers:</h4>
                    <div class="p-2 space-y-1 overflow-y-auto border rounded max-h-40">
                        @foreach ($enteredSerials as $index => $serial)
                            <div class="flex items-center justify-between p-2 rounded bg-gray-50">
                                <span class="font-mono text-sm">{{ $serial }}</span>
                                <x-mary-button wire:click="removeSerialNumber({{ $index }})"
                                    class="text-red-500 btn-xs btn-ghost" icon="o-trash" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Progress indicator --}}
            <div class="w-full h-2 bg-gray-200 rounded-full">
                <div class="h-2 transition-all duration-300 rounded-full bg-primary"
                    style="width: {{ count($enteredSerials) > 0 ? (count($enteredSerials) / $requiredSerials) * 100 : 0 }}%">
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showSerialModal', false)" />
            @if (count($enteredSerials) === $requiredSerials)
                <x-mary-button label="Save Serials" wire:click="saveSerialNumbers" class="btn-primary" />
            @else
                <x-mary-button label="Save Serials" class="btn-primary btn-disabled" disabled />
            @endif
        </x-slot:actions>
    </x-mary-modal>

    {{-- Service Modal --}}
    <x-mary-modal wire:model="showServiceModal" title="Browse Services" box-class="w-11/12 max-w-4xl">
        <div class="space-y-4">
            <x-mary-input wire:model.live.debounce.300ms="searchService" placeholder="Search services..."
                icon="o-magnifying-glass" />

            @if (!empty($serviceResults))
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach ($serviceResults as $service)
                        <div class="p-4 border rounded-lg border-base-300 hover:border-primary hover:bg-base-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold">{{ $service->name }}</h4>
                                    <p class="text-sm text-base-content/60">{{ $service->code }}</p>
                                    @if ($service->description)
                                        <p class="mt-1 text-sm text-base-content/70">{{ $service->description }}</p>
                                    @endif
                                    <div class="flex gap-2 mt-2">
                                        <span
                                            class="badge badge-sm badge-outline">{{ $service->service_type }}</span>
                                        <span
                                            class="badge badge-sm badge-outline">{{ $service->formatted_duration }}</span>
                                        @if ($service->requires_parts)
                                            <span class="badge badge-sm badge-warning">Requires Parts</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-primary">{{ $service->formatted_price }}</div>
                                    <x-mary-button wire:click="addServiceToCart({{ $service->id }})"
                                        class="mt-2 btn-sm btn-primary">
                                        Add to Cart
                                    </x-mary-button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-base-content/60">
                    @if (strlen($searchService) >= 2)
                        No services found matching "{{ $searchService }}"
                    @else
                        Enter at least 2 characters to search services
                    @endif
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-mary-button wire:click="closeServiceModal">Close</x-mary-button>
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
