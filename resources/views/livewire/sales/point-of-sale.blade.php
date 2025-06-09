<div class="min-h-screen bg-base-200">
    {{-- Header --}}
    <div class="p-4 border-b bg-base-100">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Point of Sale</h1>
            <div class="flex items-center gap-4">
                <x-mary-select :options="$warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name])" wire:model.live="selectedWarehouse" placeholder="Select Warehouse"
                    option-value="value" option-label="label" />
                <x-mary-button icon="o-qr-code" wire:click="openBarcodeModal" class="btn-ghost" tooltip="Scan Barcode" />
                <x-mary-button icon="o-arrow-path" wire:click="resetSale" class="btn-ghost" tooltip="New Sale" />
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 p-6 h-[calc(100vh-120px)]">
        {{-- Left Panel - Product Search & Cart --}}
        <div class="col-span-8 space-y-6">
            {{-- Product Search --}}
            <x-mary-card title="Add Products" class="h-fit">
                <div class="relative">
                    <x-mary-input placeholder="Search products by name, SKU, or barcode..."
                        wire:model.live.debounce="searchProduct" clearable icon="o-magnifying-glass" />

                    {{-- Search Results Dropdown --}}
                    @if (count($searchResults) > 0)
                        <div
                            class="absolute z-10 w-full mt-1 overflow-y-auto border rounded-lg shadow-lg bg-base-100 max-h-60">
                            @foreach ($searchResults as $product)
                                <div class="p-3 border-b cursor-pointer hover:bg-base-200 last:border-b-0"
                                    wire:click="addToCart({{ $product['id'] }})">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-medium">{{ $product['name'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $product['sku'] }} •
                                                ₱{{ number_format($product['selling_price'], 2) }}</div>
                                        </div>
                                        <div class="text-right">
                                            @php
                                                $stock = collect($product['inventory'])->first();
                                                $available = $stock ? $stock['quantity_available'] : 0;
                                            @endphp
                                            <div class="text-sm {{ $available > 0 ? 'text-success' : 'text-error' }}">
                                                {{ $available }} available
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
            <x-mary-card title="Shopping Cart" class="flex-1">
                @if (count($cartItems) > 0)
                    <div class="space-y-3 overflow-y-auto max-h-96">
                        @foreach ($cartItems as $key => $item)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-base-200">
                                <div class="flex-1">
                                    <div class="font-medium">{{ $item['name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $item['sku'] }}</div>
                                    <div class="text-xs text-gray-400">Available: {{ $item['available_stock'] }}</div>
                                </div>

                                <div class="flex items-center gap-3">
                                    {{-- Editable Price --}}
                                    <div class="text-center">
                                        <label class="block text-xs text-gray-500">Price</label>
                                        <input type="number" step="0.01" min="0" value="{{ $item['price'] }}"
                                            wire:blur="updatePrice('{{ $key }}', $event.target.value)"
                                            class="w-20 input input-xs input-bordered text-center" />
                                    </div>

                                    {{-- Quantity Controls --}}
                                    <div class="flex items-center gap-2">
                                        <x-mary-button icon="o-minus"
                                            wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})"
                                            class="btn-ghost btn-xs" />
                                        <span
                                            class="font-semibold min-w-[2rem] text-center">{{ $item['quantity'] }}</span>
                                        <x-mary-button icon="o-plus"
                                            wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                            class="btn-ghost btn-xs" />
                                    </div>

                                    {{-- Subtotal --}}
                                    <div class="text-right min-w-[4rem]">
                                        <div class="font-semibold">₱{{ number_format($item['subtotal'], 2) }}</div>
                                    </div>

                                    {{-- Remove Button --}}
                                    <x-mary-button icon="o-trash" wire:click="removeFromCart('{{ $key }}')"
                                        class="btn-ghost btn-xs text-error" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 mt-4 border-t">
                        <x-mary-button label="Clear Cart" wire:click="clearCart" class="btn-outline btn-sm" />
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-shopping-cart class="w-12 h-12 mx-auto text-gray-400" />
                        <p class="mt-2 text-gray-500">Cart is empty</p>
                        <p class="text-sm text-gray-400">Search and add products to get started</p>
                    </div>
                @endif
            </x-mary-card>
        </div>

        {{-- Right Panel - Customer & Checkout --}}
        <div class="col-span-4 space-y-6">
            {{-- Customer Selection --}}
            <x-mary-card title="Customer" class="h-fit">
                <div class="space-y-3">
                    <x-mary-select :options="$customers->map(fn($c) => ['value' => $c->id, 'label' => $c->name])" wire:model="selectedCustomer" placeholder="Walk-in Customer"
                        option-value="value" option-label="label" />

                    <div class="flex gap-2">
                        <x-mary-button label="New Customer" wire:click="openCustomerModal"
                            class="flex-1 btn-outline btn-sm" />
                        <x-mary-button icon="o-magnifying-glass" wire:click="openSearchCustomerModal"
                            class="btn-ghost btn-sm" tooltip="Search Customer" />
                    </div>
                </div>
            </x-mary-card>

            {{-- Order Summary --}}
            <x-mary-card title="Order Summary" class="flex-1">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>₱{{ number_format($subtotal, 2) }}</span>
                    </div>

                    @if ($discountAmount > 0)
                        <div class="flex justify-between text-warning">
                            <span>Discount:</span>
                            <div class="flex items-center gap-2">
                                <span>-₱{{ number_format($discountAmount, 2) }}</span>
                                <x-mary-button icon="o-x-mark" wire:click="removeDiscount"
                                    class="btn-ghost btn-xs text-error" tooltip="Remove Discount" />
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <span>Tax ({{ $taxRate * 100 }}%):</span>
                        <span>₱{{ number_format($taxAmount, 2) }}</span>
                    </div>

                    <div class="my-2 divider"></div>

                    <div class="flex justify-between text-lg font-bold">
                        <span>Total:</span>
                        <span>₱{{ number_format($totalAmount, 2) }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-6 space-y-3">
                    <x-mary-button label="Apply Discount" wire:click="openDiscountModal"
                        class="w-full btn-outline" />
                    <x-mary-button label="Hold Sale" wire:click="openHoldSaleModal" class="w-full btn-ghost" />
                    <x-mary-button label="Process Payment" wire:click="openPaymentModal"
                        class="w-full btn-primary btn-lg" :disabled="count($cartItems) === 0" />
                </div>

                {{-- Quick Payment Button --}}
                <div class="mt-4">
                    <x-mary-button label="Exact Cash" wire:click="setExactCash" class="w-full btn-success btn-sm"
                        :disabled="count($cartItems) === 0" />
                </div>
            </x-mary-card>
        </div>
    </div>

    {{-- Payment Modal --}}
    <x-mary-modal wire:model="showPaymentModal" title="Process Payment" subtitle="Complete the sale transaction">

        <div class="space-y-4">
            {{-- Order Summary --}}
            <div class="p-4 rounded-lg bg-base-200">
                <h4 class="mb-2 font-semibold">Order Summary</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span>Items ({{ count($cartItems) }}):</span>
                        <span>₱{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tax:</span>
                        <span>₱{{ number_format($taxAmount, 2) }}</span>
                    </div>
                    <div class="my-1 divider"></div>
                    <div class="flex justify-between font-bold">
                        <span>Total:</span>
                        <span>₱{{ number_format($totalAmount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Method --}}
            <x-mary-select label="Payment Method" :options="[
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'gcash', 'label' => 'GCash'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
            ]" wire:model="paymentMethod" option-value="value"
                option-label="label" />

            {{-- Payment Amount --}}
            <x-mary-input label="Amount Paid" wire:model.live="paidAmount" type="number" step="0.01" />

            {{-- Change --}}
            @if ($changeAmount > 0)
                <div class="p-3 rounded-lg bg-success/10">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Change:</span>
                        <span class="text-xl font-bold text-success">₱{{ number_format($changeAmount, 2) }}</span>
                    </div>
                </div>
            @endif

            {{-- Notes --}}
            <x-mary-textarea label="Sale Notes (Optional)" wire:model="saleNotes" placeholder="Additional notes..."
                rows="2" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showPaymentModal', false)" />
            <x-mary-button label="Complete Sale" wire:click="completeSale" class="btn-success" :disabled="$paidAmount < $totalAmount" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- New Customer Modal --}}
    <x-mary-modal wire:model="showCustomerModal" title="Create New Customer"
        subtitle="Add a new customer to the system">
        <div class="space-y-4">
            <x-mary-input label="Customer Name" wire:model="customerName" placeholder="Enter customer name" />
            <x-mary-input label="Email Address" wire:model="customerEmail" placeholder="customer@example.com" />
            <x-mary-input label="Phone Number" wire:model="customerPhone" placeholder="Contact number" />
            <x-mary-textarea label="Address" wire:model="customerAddress" placeholder="Customer address"
                rows="2" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showCustomerModal', false)" />
            <x-mary-button label="Create Customer" wire:click="createCustomer" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Search Customer Modal --}}
    <x-mary-modal wire:model="showSearchCustomerModal" title="Search Customer" subtitle="Find existing customer">
        <div class="space-y-4">
            <x-mary-input label="Search" wire:model.live.debounce="customerSearch"
                placeholder="Search by name, email, or phone..." icon="o-magnifying-glass" />

            @if (count($customerSearchResults) > 0)
                <div class="max-h-60 overflow-y-auto space-y-2">
                    @foreach ($customerSearchResults as $customer)
                        <div class="p-3 border rounded-lg cursor-pointer hover:bg-base-200"
                            wire:click="selectSearchedCustomer({{ $customer['id'] }})">
                            <div class="font-medium">{{ $customer['name'] }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $customer['email'] ?? 'No email' }} • {{ $customer['phone'] ?? 'No phone' }}
                            </div>
                            @if ($customer['total_orders'] > 0)
                                <div class="text-xs text-gray-400">
                                    {{ $customer['total_orders'] }} orders •
                                    ₱{{ number_format($customer['total_purchases'], 2) }} total
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @elseif(strlen($customerSearch) >= 2)
                <div class="text-center py-4 text-gray-500">
                    No customers found matching "{{ $customerSearch }}"
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showSearchCustomerModal', false)" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Apply Discount Modal --}}
    <x-mary-modal wire:model="showDiscountModal" title="Apply Discount" subtitle="Add discount to the current sale">
        <div class="space-y-4">
            <x-mary-select label="Discount Type" :options="[
                ['value' => 'percentage', 'label' => 'Percentage (%)'],
                ['value' => 'fixed', 'label' => 'Fixed Amount (₱)'],
            ]" wire:model="discountType" option-value="value"
                option-label="label" />

            <x-mary-input label="Discount Value" wire:model="discountValue" type="number" step="0.01"
                min="0"
                placeholder="{{ $discountType === 'percentage' ? 'Enter percentage (e.g., 10)' : 'Enter amount (e.g., 100.00)' }}" />

            @if ($discountType === 'percentage' && $discountValue)
                <div class="p-3 rounded-lg bg-info/10">
                    <div class="text-sm">
                        <strong>Preview:</strong> {{ $discountValue }}% discount =
                        ₱{{ number_format($subtotal * ($discountValue / 100), 2) }}
                    </div>
                </div>
            @elseif ($discountType === 'fixed' && $discountValue)
                <div class="p-3 rounded-lg bg-info/10">
                    <div class="text-sm">
                        <strong>Preview:</strong> ₱{{ number_format(min($discountValue, $subtotal), 2) }} discount
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
    <x-mary-modal wire:model="showHoldSaleModal" title="Hold Sale" subtitle="Save current sale for later completion">
        <div class="space-y-4">
            <div class="p-4 rounded-lg bg-warning/10">
                <div class="flex items-center gap-2 text-warning-700">
                    <x-heroicon-o-information-circle class="w-5 h-5" />
                    <span class="font-medium">Sale will be saved as draft and can be retrieved later</span>
                </div>
            </div>

            <x-mary-input label="Hold Reference" wire:model="holdReference" placeholder="Reference number" />
            <x-mary-textarea label="Notes" wire:model="holdNotes" placeholder="Optional notes for this held sale"
                rows="3" />

            <div class="p-3 rounded-lg bg-base-200">
                <div class="text-sm space-y-1">
                    <div class="flex justify-between">
                        <span>Items:</span>
                        <span>{{ count($cartItems) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>₱{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold">
                        <span>Total:</span>
                        <span>₱{{ number_format($totalAmount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showHoldSaleModal', false)" />
            <x-mary-button label="Hold Sale" wire:click="holdSale" class="btn-warning" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Barcode Scanner Modal --}}
    <x-mary-modal wire:model="showBarcodeModal" title="Barcode Scanner"
        subtitle="Scan multiple items then add to cart">
        <div class="space-y-4">

            {{-- Barcode Input --}}
            <div>
                <x-mary-input label="Barcode Scanner" wire:model.live="barcodeInput"
                    placeholder="Scan barcode here..." id="barcode-input" />
                <div class="mt-2 text-xs text-gray-500">
                    Scan multiple items to build your batch. Items will be added to cart when you click "Add to Cart"
                    below.
                </div>
            </div>

            {{-- Scanned Items (Batch) --}}
            <div class="p-3 rounded-lg bg-base-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium">Scanned Items</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">{{ count($scannedItems) }} items</span>
                        @if (count($scannedItems) > 0)
                            <x-mary-button icon="o-trash" wire:click="clearScannedItems"
                                class="btn-ghost btn-xs text-error" tooltip="Clear All" />
                        @endif
                    </div>
                </div>

                @if (count($scannedItems) > 0)
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        @foreach ($scannedItems as $index => $item)
                            <div class="flex items-center justify-between p-2 rounded bg-base-100">
                                <div class="flex-1">
                                    <div class="font-medium text-sm">{{ $item['name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $item['sku'] }} • Stock:
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
                                        <div class="font-semibold text-sm">₱{{ number_format($item['subtotal'], 2) }}
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
                    <div class="mt-3 pt-2 border-t border-gray-300">
                        <div class="flex justify-between font-semibold text-sm">
                            <span>Batch Total:</span>
                            <span>₱{{ number_format(collect($scannedItems)->sum('subtotal'), 2) }}</span>
                        </div>
                    </div>
                @else
                    <div class="text-xs text-gray-500 text-center py-4">No items scanned yet</div>
                @endif
            </div>

            {{-- Current Cart Summary --}}
            <div class="p-3 rounded-lg bg-primary/5 border border-primary/20">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-primary-700">Current Cart</span>
                    <span class="text-xs text-primary-600">{{ count($cartItems) }} items</span>
                </div>
                @if (count($cartItems) > 0)
                    <div class="space-y-1 max-h-20 overflow-y-auto">
                        @foreach (array_slice($cartItems, -3, 3, true) as $key => $item)
                            <div class="flex justify-between text-xs text-primary-700">
                                <span class="truncate">{{ $item['name'] }}</span>
                                <span>{{ $item['quantity'] }}x ₱{{ number_format($item['price'], 2) }}</span>
                            </div>
                        @endforeach
                        @if (count($cartItems) > 3)
                            <div class="text-xs text-primary-600 text-center">... and {{ count($cartItems) - 3 }} more
                                items</div>
                        @endif
                    </div>
                    <div class="mt-2 pt-2 border-t border-primary/20">
                        <div class="flex justify-between font-semibold text-sm text-primary-800">
                            <span>Cart Total:</span>
                            <span>₱{{ number_format($totalAmount, 2) }}</span>
                        </div>
                    </div>
                @else
                    <div class="text-xs text-primary-600 text-center py-2">Cart is empty</div>
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

    <script>
        // Global variable to track Livewire component
        let posComponent = null;

        // Initialize when Livewire loads
        document.addEventListener('livewire:init', () => {
            // Get the Livewire component instance
            posComponent = @this;

            // Listen for modal changes and auto-focus
            Livewire.hook('morph.updated', () => {
                focusBarcodeInput();
            });
        });

        function focusBarcodeInput() {
            const input = document.getElementById('barcode-input');
            if (input && document.querySelector('[wire\\:model="showBarcodeModal"]')) {
                setTimeout(() => {
                    input.focus();
                    input.select();
                }, 100);
            }
        }

        // Enhanced global keyboard event handler
        document.addEventListener('keydown', function(e) {
            // Ctrl+B shortcut - multiple detection methods
            if (e.ctrlKey && (e.key === 'b' || e.key === 'B' || e.keyCode === 66)) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Ctrl+B detected'); // Debug log

                // Try multiple methods to call the Livewire method
                try {
                    if (posComponent) {
                        posComponent.call('openBarcodeModal');
                    } else if (window.Livewire) {
                        // Fallback 1: Find component by wire:id
                        const wireElement = document.querySelector('[wire\\:id]');
                        if (wireElement) {
                            const wireId = wireElement.getAttribute('wire:id');
                            window.Livewire.find(wireId).call('openBarcodeModal');
                        }
                    } else if (typeof @this !== 'undefined') {
                        // Fallback 2: Direct @this reference
                        @this.call('openBarcodeModal');
                    }
                } catch (error) {
                    console.error('Error opening barcode modal:', error);
                    // Last resort: trigger click on barcode button
                    const barcodeButton = document.querySelector('[wire\\:click="openBarcodeModal"]');
                    if (barcodeButton) {
                        barcodeButton.click();
                    }
                }

                return false;
            }

            // F9 as alternative shortcut
            if (e.key === 'F9') {
                e.preventDefault();
                try {
                    if (posComponent) {
                        posComponent.call('openBarcodeModal');
                    }
                } catch (error) {
                    console.error('Error with F9 shortcut:', error);
                }
                return false;
            }

            // Escape to close modal (only if barcode modal is open)
            if (e.key === 'Escape') {
                const barcodeInput = document.getElementById('barcode-input');
                if (barcodeInput && document.activeElement === barcodeInput) {
                    try {
                        if (posComponent) {
                            posComponent.set('showBarcodeModal', false);
                        }
                    } catch (error) {
                        console.error('Error closing modal:', error);
                    }
                }
            }
        }, true); // Use capture phase

        // Alternative event listener for better browser compatibility
        document.addEventListener('keyup', function(e) {
            if (e.ctrlKey && (e.key === 'b' || e.key === 'B' || e.keyCode === 66)) {
                e.preventDefault();
                console.log('Ctrl+B keyup detected'); // Debug log
            }
        });

        // Focus when modal opens via Livewire events
        document.addEventListener('livewire:load', function() {
            Livewire.on('barcode-modal-opened', () => {
                setTimeout(focusBarcodeInput, 150);
            });
        });

        // Enhanced focus management with MutationObserver
        const focusObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                // Check if barcode input was added to DOM
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) { // Element node
                            const barcodeInput = node.querySelector?.('#barcode-input') ||
                                (node.id === 'barcode-input' ? node : null);
                            if (barcodeInput) {
                                setTimeout(() => {
                                    barcodeInput.focus();
                                    barcodeInput.select();
                                }, 50);
                            }
                        }
                    });
                }
            });
        });

        // Start observing DOM changes
        focusObserver.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Additional debugging and fallback methods
        window.openBarcodeModal = function() {
            try {
                if (posComponent) {
                    posComponent.call('openBarcodeModal');
                } else {
                    console.log('No posComponent available');
                }
            } catch (error) {
                console.error('Manual modal open error:', error);
            }
        };

        // Test function for debugging
        window.testShortcut = function() {
            console.log('Testing Ctrl+B shortcut...');
            const event = new KeyboardEvent('keydown', {
                key: 'b',
                ctrlKey: true,
                bubbles: true,
                cancelable: true
            });
            document.dispatchEvent(event);
        };

        // Ensure focus periodically if modal is open
        setInterval(() => {
            const input = document.getElementById('barcode-input');
            if (input && input.offsetParent !== null) { // Check if visible
                const modal = input.closest('.modal');
                if (modal && !modal.classList.contains('hidden') && document.activeElement !== input) {
                    input.focus();
                }
            }
        }, 2000);
    </script>
</div>
