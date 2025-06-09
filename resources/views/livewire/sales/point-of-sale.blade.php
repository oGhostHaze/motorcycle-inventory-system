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
    <x-mary-modal wire:model="showBarcodeModal" title="Barcode Scanner" subtitle="Scan or enter product barcode">
        <div class="space-y-4">
            <div class="p-4 rounded-lg bg-info/10">
                <div class="flex items-center gap-2 text-info-700">
                    <x-heroicon-o-qr-code class="w-5 h-5" />
                    <span class="font-medium">Scan barcode with device camera or enter manually</span>
                </div>
            </div>

            {{-- Manual Barcode Input --}}
            <div>
                <x-mary-input label="Barcode" wire:model="barcodeInput" placeholder="Enter or scan barcode here..."
                    wire:keydown.enter="processBarcodeInput" autofocus />
                <div class="mt-2 text-xs text-gray-500">
                    Tip: Focus this field and use your barcode scanner, or type the barcode manually
                </div>
            </div>

            {{-- Camera Scanner (HTML5) --}}
            <div class="text-center">
                <div class="space-y-4">
                    <div class="flex gap-2 justify-center">
                        <x-mary-button label="Start Camera" onclick="startCamera()" class="btn-primary btn-sm"
                            id="startCameraBtn" />
                        <x-mary-button label="Stop Camera" onclick="stopCamera()" class="btn-secondary btn-sm"
                            id="stopCameraBtn" style="display: none;" />
                    </div>

                    {{-- Camera Video Container --}}
                    <div id="camera-container" style="display: none;" class="relative mx-auto"
                        style="width: 300px; height: 200px;">
                        <div id="scanner" class="border rounded-lg overflow-hidden bg-black"></div>
                        <div id="scan-line" class="absolute top-1/2 left-0 right-0 h-0.5 bg-red-500 animate-pulse">
                        </div>
                    </div>

                    {{-- Camera Status --}}
                    <div id="camera-status" class="text-sm text-gray-500">
                        Click "Start Camera" to begin scanning
                    </div>

                    {{-- Fallback Message --}}
                    <div id="camera-fallback" style="display: none;"
                        class="p-4 border-2 border-gray-300 border-dashed rounded-lg">
                        <x-heroicon-o-camera class="w-12 h-12 mx-auto text-gray-400 mb-2" />
                        <p class="text-sm text-gray-500 mb-3">Camera not available or not supported</p>
                        <p class="text-xs text-gray-400">Use a handheld barcode scanner or enter barcode manually above
                        </p>
                    </div>
                </div>
            </div>

            {{-- Recent Scans (if any) --}}
            <div class="p-3 rounded-lg bg-base-200">
                <div class="text-sm font-medium mb-2">Quick Actions:</div>
                <div class="grid grid-cols-2 gap-2">
                    <x-mary-button label="Test: 123456789" wire:click="$set('barcodeInput', '123456789')"
                        class="btn-xs btn-outline" />
                    <x-mary-button label="Test: 987654321" wire:click="$set('barcodeInput', '987654321')"
                        class="btn-xs btn-outline" />
                </div>
                <div class="mt-2">
                    <x-mary-button label="Process Test Barcode" wire:click="processBarcodeInput"
                        class="btn-xs btn-primary w-full" />
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showBarcodeModal', false)" />
            <x-mary-button label="Add to Cart" wire:click="processBarcodeInput" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Barcode Scanner JavaScript --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <script>
        let cameraActive = false;
        let quaggaInitialized = false;

        // Camera functions
        function startCamera() {
            const container = document.getElementById('camera-container');
            const status = document.getElementById('camera-status');
            const startBtn = document.getElementById('startCameraBtn');
            const stopBtn = document.getElementById('stopCameraBtn');
            const fallback = document.getElementById('camera-fallback');
            const scanner = document.getElementById('scanner');

            // Check if getUserMedia is supported
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showCameraFallback();
                return;
            }

            status.textContent = 'Initializing camera...';

            // Show container and hide fallback
            container.style.display = 'block';
            fallback.style.display = 'none';

            // Configure Quagga for barcode detection
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: scanner, // Use the scanner div as target
                    constraints: {
                        width: {
                            min: 300,
                            ideal: 400,
                            max: 600
                        },
                        height: {
                            min: 200,
                            ideal: 300,
                            max: 400
                        },
                        facingMode: "environment", // Use back camera on mobile
                        aspectRatio: {
                            min: 1,
                            max: 2
                        }
                    }
                },
                decoder: {
                    readers: [
                        "code_128_reader",
                        "ean_reader",
                        "ean_8_reader",
                        "code_39_reader",
                        "upc_reader",
                        "upc_e_reader"
                    ],
                    debug: {
                        showCanvas: false,
                        showPatches: false,
                        showFoundPatches: false,
                        showSkeleton: false,
                        showLabels: false,
                        showPatchLabels: false,
                        showRemainingPatchLabels: false,
                        boxFromPatches: {
                            showTransformed: false,
                            showTransformedBox: false,
                            showBB: false
                        }
                    }
                },
                locate: true,
                locator: {
                    halfSample: true,
                    patchSize: "medium",
                    debug: {
                        showCanvas: false,
                        showPatches: false,
                        showFoundPatches: false,
                        showSkeleton: false,
                        showLabels: false,
                        showPatchLabels: false,
                        showRemainingPatchLabels: false,
                        boxFromPatches: {
                            showTransformed: false,
                            showTransformedBox: false,
                            showBB: false
                        }
                    }
                },
                numOfWorkers: 2,
                frequency: 10,
                debug: false
            }, function(err) {
                if (err) {
                    console.error('Error initializing Quagga:', err);
                    status.textContent = 'Camera initialization failed';
                    showCameraFallback();
                    return;
                }

                console.log("Quagga initialization finished successfully");

                try {
                    Quagga.start();

                    // Update UI
                    startBtn.style.display = 'none';
                    stopBtn.style.display = 'inline-block';
                    status.textContent = 'Camera active - Point at barcode to scan';
                    cameraActive = true;
                    quaggaInitialized = true;

                } catch (startErr) {
                    console.error('Error starting Quagga:', startErr);
                    status.textContent = 'Failed to start camera';
                    showCameraFallback();
                }
            });

            // Listen for successful barcode detection
            Quagga.onDetected(function(result) {
                if (result && result.codeResult && result.codeResult.code) {
                    const barcode = result.codeResult.code;
                    console.log('Barcode detected:', barcode);

                    // Provide immediate feedback
                    status.textContent = `Scanned: ${barcode} - Processing...`;

                    // Set the barcode input and process it
                    @this.set('barcodeInput', barcode);
                    @this.call('processBarcodeInput');

                    // Stop camera after successful scan
                    setTimeout(() => {
                        stopCamera();
                    }, 1500);
                }
            });

            // Handle any processing errors
            Quagga.onProcessed(function(result) {
                // Optional: Could add visual feedback here
            });
        }

        function stopCamera() {
            const container = document.getElementById('camera-container');
            const status = document.getElementById('camera-status');
            const startBtn = document.getElementById('startCameraBtn');
            const stopBtn = document.getElementById('stopCameraBtn');

            try {
                if (quaggaInitialized) {
                    Quagga.stop();
                    Quagga.offDetected();
                    Quagga.offProcessed();
                    quaggaInitialized = false;
                }
            } catch (err) {
                console.error('Error stopping camera:', err);
            }

            container.style.display = 'none';
            startBtn.style.display = 'inline-block';
            stopBtn.style.display = 'none';
            status.textContent = 'Camera stopped - Click "Start Camera" to scan again';
            cameraActive = false;
        }

        function showCameraFallback() {
            const container = document.getElementById('camera-container');
            const fallback = document.getElementById('camera-fallback');
            const status = document.getElementById('camera-status');
            const startBtn = document.getElementById('startCameraBtn');
            const stopBtn = document.getElementById('stopCameraBtn');

            container.style.display = 'none';
            fallback.style.display = 'block';
            startBtn.style.display = 'inline-block';
            stopBtn.style.display = 'none';
            status.textContent = 'Camera not available - Use manual input instead';
        }

        // Auto-stop camera when modal closes
        document.addEventListener('livewire:init', () => {
            // Auto-focus barcode input when modal opens
            document.addEventListener('livewire:navigated', () => {
                const input = document.querySelector('[wire\\:model="barcodeInput"]');
                if (input) {
                    setTimeout(() => input.focus(), 100);
                }
            });
        });

        // Global barcode handler - Ctrl+B to open scanner
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                @this.call('openBarcodeModal');
            }
        });

        // Clean up when page unloads or modal closes
        window.addEventListener('beforeunload', () => {
            if (cameraActive) {
                stopCamera();
            }
        });

        // Stop camera when navigating away
        document.addEventListener('livewire:navigating', () => {
            if (cameraActive) {
                stopCamera();
            }
        });
    </script>
</div>
