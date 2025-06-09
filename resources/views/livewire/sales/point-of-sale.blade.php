<div class="min-h-screen bg-base-200">
    {{-- Header --}}
    <div class="p-4 border-b bg-base-100">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Point of Sale</h1>
            <div class="flex items-center gap-4">
                <x-mary-select :options="$warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name])" wire:model.live="selectedWarehouse" placeholder="Select Warehouse"
                    option-value="value" option-label="label" />
                <x-mary-button icon="o-qr-code" class="btn-ghost" tooltip="Scan Barcode" />
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
                                    <div class="text-sm text-gray-500">{{ $item['sku'] }} •
                                        ₱{{ number_format($item['price'], 2) }}</div>
                                    <div class="text-xs text-gray-400">Available: {{ $item['available_stock'] }}</div>
                                </div>

                                <div class="flex items-center gap-3">
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

                                    <div class="text-right min-w-[4rem]">
                                        <div class="font-semibold">₱{{ number_format($item['subtotal'], 2) }}</div>
                                    </div>

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
                        <x-mary-button label="New Customer" class="flex-1 btn-outline btn-sm" />
                        <x-mary-button icon="o-magnifying-glass" class="btn-ghost btn-sm" tooltip="Search Customer" />
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
                            <span>-₱{{ number_format($discountAmount, 2) }}</span>
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
                    <x-mary-button label="Apply Discount" class="w-full btn-outline" />
                    <x-mary-button label="Hold Sale" class="w-full btn-ghost" />
                    <x-mary-button label="Process Payment" wire:click="openPaymentModal"
                        class="w-full btn-primary btn-lg" :disabled="count($cartItems) === 0" />
                </div>

                {{-- Quick Payment Buttons --}}
                <div class="grid grid-cols-2 gap-2 mt-4">
                    <x-mary-button label="Exact Cash" class="btn-success btn-sm"
                        wire:click="$set('paidAmount', {{ $totalAmount }})" />
                    <x-mary-button label="Card Payment" class="btn-info btn-sm" />
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
                ['value' => 'card', 'label' => 'Credit/Debit Card'],
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
</div>
