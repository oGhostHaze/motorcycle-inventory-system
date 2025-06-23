<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesShift;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mary\Traits\Toast;

class PointOfSale extends Component
{
    use Toast;

    // Current active shift
    public ?SalesShift $currentShift = null;

    // Cart and sale data
    public $cartItems = [];
    public $subtotal = 0;
    public $discountAmount = 0;
    public $taxAmount = 0;
    public $totalAmount = 0;
    public $paidAmount = 0;
    public $changeAmount = 0;

    // Form fields
    public $searchProduct = '';
    public $selectedCustomer = null;
    public $selectedWarehouse = '';
    public $paymentMethod = 'cash';
    public $saleNotes = '';

    // UI state
    public $showCustomerModal = false;
    public $showPaymentModal = false;
    public $showDiscountModal = false;
    public $showHoldSaleModal = false;
    public $showSearchCustomerModal = false;
    public $showStartShiftModal = false;
    public $searchResults = [];

    // Customer form fields
    public $customerName = '';
    public $customerEmail = '';
    public $customerPhone = '';
    public $customerAddress = '';

    // Hold sale fields
    public $holdReference = '';
    public $holdNotes = '';

    // Discount fields
    public $discountType = 'percentage'; // percentage or fixed
    public $discountValue = '';

    // Customer search
    public $customerSearch = '';
    public $customerSearchResults = [];

    // Barcode scanning
    public $showBarcodeModal = false;
    public $barcodeInput = '';
    public $scannedItems = [];

    // Shift start fields
    public $openingCash = '';
    public $openingNotes = '';

    // Tax rate (configurable)
    public $taxRate = 0; // 12% VAT

    public $showHeldSalesModal = false;
    public $heldSales = [];

    // Price selection properties
    public $showPriceModal = false;
    public $showBulkPriceModal = false;
    public $selectedCartIndex = null;
    public $availablePrices = [];
    public $showAddPriceModal = false;
    public $pendingProductId = null;
    public $bulkPriceType = 'selling_price';

    public function mount()
    {
        $this->loadCurrentShift();

        // Set default warehouse from current shift or first available
        if ($this->currentShift) {
            $this->selectedWarehouse = $this->currentShift->warehouse_id;
        } else {
            $this->selectedWarehouse = Warehouse::where('is_active', true)->first()?->id;
        }

        $this->loadHeldSales();
    }

    public function loadCurrentShift()
    {
        $this->currentShift = SalesShift::getActiveShift(auth()->id());
    }

    public function render()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('livewire.sales.point-of-sale', [
            'warehouses' => $warehouses,
            'customers' => $customers,
        ])->layout('layouts.pos', ['title' => 'Point of Sale']);
    }

    // ===== SHIFT MANAGEMENT METHODS =====
    public function openStartShiftModal()
    {
        if ($this->currentShift) {
            $this->error('You already have an active shift.');
            return;
        }

        $this->openingCash = '';
        $this->openingNotes = '';
        $this->showStartShiftModal = true;
    }

    public function startShift()
    {
        $this->validate([
            'selectedWarehouse' => 'required|exists:warehouses,id',
            'openingCash' => 'required|numeric|min:0',
            'openingNotes' => 'nullable|string|max:500',
        ]);

        // Check for existing active shift
        if (SalesShift::hasActiveShift(auth()->id())) {
            $this->error('You already have an active shift.');
            return;
        }

        try {
            $this->currentShift = SalesShift::create([
                'user_id' => auth()->id(),
                'warehouse_id' => $this->selectedWarehouse,
                'started_at' => now(),
                'opening_cash' => $this->openingCash,
                'opening_notes' => $this->openingNotes,
                'status' => 'active',
            ]);

            $this->success('Shift started successfully! You can now process sales.');
            $this->showStartShiftModal = false;
        } catch (\Exception $e) {
            $this->error('Error starting shift: ' . $e->getMessage());
        }
    }

    private function checkActiveShift()
    {
        if (!$this->currentShift) {
            $this->error('No active shift found. Please start a shift before processing sales.');
            return false;
        }
        return true;
    }

    // ===== EXISTING POS METHODS (UPDATED) =====
    public function updatedSearchProduct()
    {
        if (!$this->checkActiveShift()) return;

        if (strlen($this->searchProduct) >= 2) {
            $this->searchResults = Product::where('status', 'active')
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->searchProduct . '%')
                        ->orWhere('sku', 'like', '%' . $this->searchProduct . '%')
                        ->orWhere('barcode', 'like', '%' . $this->searchProduct . '%')
                        ->orWhereHas('brand', function ($brandQuery) {
                            $brandQuery->where('name', 'like', '%' . $this->searchProduct . '%');
                        });
                })
                ->with(['inventory' => function ($query) {
                    $query->where('warehouse_id', $this->selectedWarehouse);
                }])
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->searchResults = [];
        }
    }

    /**
     * Open price selection for individual cart item
     */
    public function openPriceSelection($cartKey)
    {
        if (!isset($this->cartItems[$cartKey])) {
            return;
        }

        $product = Product::find($this->cartItems[$cartKey]['product_id']);
        if (!$product) {
            return;
        }

        $this->selectedCartIndex = $cartKey;
        $this->availablePrices = $product->getAvailablePrices();
        $this->showPriceModal = true;
    }

    /**
     * Select price for individual cart item
     */
    public function selectPrice($priceType)
    {
        if (!$this->selectedCartIndex || !isset($this->availablePrices[$priceType])) {
            return;
        }

        $newPrice = $this->availablePrices[$priceType]['value'];
        $priceLabel = $this->availablePrices[$priceType]['label']; // Store label before clearing

        $this->cartItems[$this->selectedCartIndex]['price'] = $newPrice;
        $this->cartItems[$this->selectedCartIndex]['subtotal'] =
            $this->cartItems[$this->selectedCartIndex]['quantity'] * $newPrice;

        $this->updateCartTotals();
        $this->showPriceModal = false;
        $this->selectedCartIndex = null;
        $this->availablePrices = []; // Clear after storing the label

        $this->success('Price updated to ' . $priceLabel . ': ₱' . number_format($newPrice, 2));
    }

    /**
     * Open bulk price selection for all cart items
     */
    public function openBulkPriceSelection()
    {
        if (empty($this->cartItems)) {
            $this->error('Cart is empty.');
            return;
        }

        $this->bulkPriceType = 'selling_price';
        $this->showBulkPriceModal = true;
    }

    /**
     * Apply bulk price change to all compatible items
     */
    public function applyBulkPrice()
    {
        if (empty($this->cartItems)) {
            return;
        }

        $updatedCount = 0;

        foreach ($this->cartItems as $cartKey => $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $newPrice = null;
            switch ($this->bulkPriceType) {
                case 'selling_price':
                    $newPrice = $product->selling_price;
                    break;
                case 'wholesale_price':
                    $newPrice = $product->wholesale_price;
                    break;
                case 'alt_price1':
                    $newPrice = $product->alt_price1;
                    break;
                case 'alt_price2':
                    $newPrice = $product->alt_price2;
                    break;
                case 'alt_price3':
                    $newPrice = $product->alt_price3;
                    break;
            }

            if ($newPrice > 0) {
                $this->cartItems[$cartKey]['price'] = $newPrice;
                $this->cartItems[$cartKey]['subtotal'] =
                    $this->cartItems[$cartKey]['quantity'] * $newPrice;
                $updatedCount++;
            }
        }

        $this->updateCartTotals();
        $this->showBulkPriceModal = false;

        if ($updatedCount > 0) {
            $this->success($updatedCount . ' item(s) price updated successfully!');
        } else {
            $this->warning('No items were updated. Selected price type may not be available for current products.');
        }
    }

    /**
     * Enhanced addToCart with price selection option
     */
    public function addToCartWithPriceSelection($productId)
    {
        if (!$this->checkActiveShift()) return;

        $product = Product::find($productId);
        if (!$product) {
            $this->error('Product not found.');
            return;
        }

        $availablePrices = $product->getAvailablePrices();

        // If only one price is available, add directly
        if (count($availablePrices) <= 1) {
            $this->addToCart($productId);
            return;
        }

        // Show price selection modal
        $this->pendingProductId = $productId;
        $this->availablePrices = $availablePrices;
        $this->showAddPriceModal = true;
    }

    /**
     * Add to cart with selected price
     */
    public function addToCartWithPrice($priceType)
    {
        if (!$this->pendingProductId || !isset($this->availablePrices[$priceType])) {
            return;
        }

        $product = Product::with(['inventory' => function ($query) {
            $query->where('warehouse_id', $this->selectedWarehouse);
        }])->find($this->pendingProductId);

        if (!$product) {
            $this->error('Product not found.');
            return;
        }

        $inventory = $product->inventory->first();
        $availableStock = $inventory ? $inventory->quantity_available : 0;

        if ($availableStock <= 0) {
            $this->error('Product is out of stock.');
            return;
        }

        $selectedPrice = $this->availablePrices[$priceType]['value'];
        $priceLabel = $this->availablePrices[$priceType]['label']; // Store label before clearing
        $cartKey = $this->pendingProductId;

        if (isset($this->cartItems[$cartKey])) {
            if ($this->cartItems[$cartKey]['quantity'] >= $availableStock) {
                $this->error('Cannot add more items. Stock limit reached.');
                return;
            }
            $this->cartItems[$cartKey]['quantity']++;
            $this->cartItems[$cartKey]['subtotal'] =
                $this->cartItems[$cartKey]['quantity'] * $this->cartItems[$cartKey]['price'];
        } else {
            $this->cartItems[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $selectedPrice,
                'quantity' => 1,
                'available_stock' => $availableStock,
                'subtotal' => $selectedPrice,
            ];
        }

        $this->updateCartTotals();
        $this->searchProduct = '';
        $this->searchResults = [];
        $this->showAddPriceModal = false;
        $this->pendingProductId = null;
        $this->availablePrices = []; // Clear after storing the label

        $this->success('Item added to cart with ' . $priceLabel . ': ₱' . number_format($selectedPrice, 2));
    }

    /**
     * Updated addToCart method (keep existing functionality)
     */
    public function addToCart($productId)
    {
        if (!$this->checkActiveShift()) return;

        $product = Product::with(['inventory' => function ($query) {
            $query->where('warehouse_id', $this->selectedWarehouse);
        }])->find($productId);

        if (!$product) {
            $this->error('Product not found.');
            return;
        }

        $inventory = $product->inventory->first();
        $availableStock = $inventory ? $inventory->quantity_available : 0;

        if ($availableStock <= 0) {
            $this->error('Product is out of stock.');
            return;
        }

        $cartKey = $productId;

        if (isset($this->cartItems[$cartKey])) {
            if ($this->cartItems[$cartKey]['quantity'] >= $availableStock) {
                $this->error('Cannot add more items. Stock limit reached.');
                return;
            }
            $this->cartItems[$cartKey]['quantity']++;
            $this->cartItems[$cartKey]['subtotal'] =
                $this->cartItems[$cartKey]['quantity'] * $this->cartItems[$cartKey]['price'];
        } else {
            $this->cartItems[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->selling_price, // Default to selling price
                'quantity' => 1,
                'available_stock' => $availableStock,
                'subtotal' => $product->selling_price,
            ];
        }

        $this->updateCartTotals();
        $this->searchProduct = '';
        $this->searchResults = [];
        $this->success('Item added to cart!');
    }

    public function updateQuantity($cartKey, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($cartKey);
            return;
        }

        if (isset($this->cartItems[$cartKey])) {
            $availableStock = $this->cartItems[$cartKey]['available_stock'];

            if ($quantity > $availableStock) {
                $this->error('Quantity exceeds available stock (' . $availableStock . ')');
                return;
            }

            $this->cartItems[$cartKey]['quantity'] = $quantity;
            $this->cartItems[$cartKey]['subtotal'] = $this->cartItems[$cartKey]['price'] * $quantity;
            $this->updateCartTotals();
        }
    }

    public function updatePrice($cartKey, $price)
    {
        if ($price < 0) {
            $this->error('Price cannot be negative');
            return;
        }

        if (isset($this->cartItems[$cartKey])) {
            $this->cartItems[$cartKey]['price'] = $price;
            $this->cartItems[$cartKey]['subtotal'] = $this->cartItems[$cartKey]['quantity'] * $price;
            $this->updateCartTotals();
            $this->success('Price updated successfully!');
        }
    }

    public function removeFromCart($cartKey)
    {
        unset($this->cartItems[$cartKey]);
        $this->updateCartTotals();
        $this->success('Item removed from cart!');
    }

    public function clearCart()
    {
        $this->cartItems = [];
        $this->discountType = null;
        $this->discountValue = 0;
        $this->discountAmount = 0;
        $this->updateCartTotals();
        $this->success('Cart cleared!');
    }

    public function updateCartTotals()
    {
        $this->subtotal = collect($this->cartItems)->sum('subtotal');

        $this->recalculateDiscount();
        $this->taxAmount = $this->subtotal * $this->taxRate;
        $this->totalAmount = $this->subtotal + $this->taxAmount - $this->discountAmount;
        $this->calculateChange();
    }

    public function recalculateDiscount()
    {
        // Only recalculate if there's an active discount
        if ($this->discountAmount > 0 && $this->discountValue > 0) {
            if ($this->discountType === 'percentage') {
                $this->discountAmount = $this->subtotal * ($this->discountValue / 100);
            } else {
                // For fixed discounts, ensure it doesn't exceed the subtotal
                $this->discountAmount = min($this->discountValue, $this->subtotal);
            }
        }
    }

    public function applyDiscount()
    {
        $this->validate([
            'discountType' => 'required|in:percentage,fixed',
            'discountValue' => 'required|numeric|min:0',
        ]);

        if ($this->discountType === 'percentage' && $this->discountValue > 100) {
            $this->error('Percentage discount cannot exceed 100%');
            return;
        }

        if ($this->discountType === 'percentage') {
            $this->discountAmount = $this->subtotal * ($this->discountValue / 100);
        } else {
            $this->discountAmount = min($this->discountValue, $this->subtotal);
        }

        $this->updateCartTotals();
        $this->showDiscountModal = false;
        $this->success('Discount applied successfully!');
    }

    public function updatedPaidAmount()
    {
        $this->calculateChange();
    }

    public function calculateChange()
    {
        $this->changeAmount = max(0, $this->paidAmount - $this->totalAmount);
    }

    public function openPaymentModal()
    {
        if (!$this->checkActiveShift()) return;

        if (empty($this->cartItems)) {
            $this->error('Cart is empty. Please add items first.');
            return;
        }

        if (!$this->selectedWarehouse) {
            $this->error('Please select a warehouse.');
            return;
        }

        $this->paidAmount = $this->totalAmount;
        $this->calculateChange();
        $this->showPaymentModal = true;
    }

    public function completeSale()
    {
        if (!$this->checkActiveShift()) return;

        if ($this->paidAmount < $this->totalAmount) {
            $this->error('Insufficient payment amount.');
            return;
        }

        try {
            DB::beginTransaction();

            // Check inventory WITHOUT locking - immediate response
            $inventoryIssues = [];
            $inventorySnapshots = [];

            foreach ($this->cartItems as $item) {
                $currentInventory = Inventory::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $this->selectedWarehouse)
                    ->first();

                $availableQty = $currentInventory ? $currentInventory->quantity_available : 0;

                // Store current state for later verification
                $inventorySnapshots[$item['product_id']] = [
                    'current_quantity' => $currentInventory ? $currentInventory->quantity_on_hand : 0,
                    'updated_at' => $currentInventory ? $currentInventory->updated_at : null
                ];

                if ($availableQty < $item['quantity']) {
                    $inventoryIssues[] = [
                        'product' => $item['name'],
                        'requested' => $item['quantity'],
                        'available' => $availableQty
                    ];
                }
            }

            // If insufficient stock, fail immediately
            if (!empty($inventoryIssues)) {
                DB::rollBack();
                $this->handleInventoryConflict($inventoryIssues);
                return;
            }

            // Create sale record
            $sale = Sale::create([
                'customer_id' => $this->selectedCustomer,
                'warehouse_id' => $this->selectedWarehouse,
                'user_id' => auth()->id(),
                'shift_id' => $this->currentShift->id,
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->discountAmount,
                'tax_amount' => $this->taxAmount,
                'total_amount' => $this->totalAmount,
                'paid_amount' => $this->paidAmount,
                'change_amount' => $this->changeAmount,
                'payment_method' => $this->paymentMethod,
                'status' => 'completed',
                'notes' => $this->saleNotes,
                'completed_at' => now(),
            ]);

            // Update inventory with optimistic locking
            foreach ($this->cartItems as $item) {
                $product = Product::find($item['product_id']);

                // Create sale item first
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['subtotal'],
                    'cost_price' => $product->cost_price ?? 0,
                ]);

                // Optimistic update with version check
                $snapshot = $inventorySnapshots[$item['product_id']];

                $updateResult = DB::table('inventories')
                    ->where('product_id', $item['product_id'])
                    ->where('warehouse_id', $this->selectedWarehouse)
                    ->where('quantity_on_hand', $snapshot['current_quantity']) // Ensure quantity hasn't changed
                    ->where('updated_at', $snapshot['updated_at']) // Ensure record hasn't been modified
                    ->update([
                        'quantity_on_hand' => $snapshot['current_quantity'] - $item['quantity'],
                        'updated_at' => now()
                    ]);

                // If update failed, someone else modified the inventory
                if ($updateResult === 0) {
                    DB::rollBack();
                    $this->error('Inventory was modified by another user. Please refresh and try again.');
                    $this->refreshCartInventory();
                    return;
                }

                // Create stock movement
                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $this->selectedWarehouse,
                    'type' => 'sale',
                    'quantity_before' => $snapshot['current_quantity'],
                    'quantity_changed' => -$item['quantity'],
                    'quantity_after' => $snapshot['current_quantity'] - $item['quantity'],
                    'unit_cost' => $product->cost_price ?? 0,
                    'reference_id' => $sale->id,
                    'reference_type' => Sale::class,
                    'user_id' => auth()->id(),
                    'notes' => 'Sale: ' . $sale->invoice_number,
                ]);
            }

            // Update customer stats
            if ($this->selectedCustomer) {
                $customer = Customer::find($this->selectedCustomer);
                $customer->increment('total_orders');
                $customer->increment('total_purchases', $this->totalAmount);
                $customer->update(['last_purchase_at' => now()]);
            }

            $this->currentShift->calculateTotals();

            DB::commit();

            $this->success('Sale completed successfully! Invoice: ' . $sale->invoice_number);
            $this->resetSale();
            $this->showPaymentModal = false;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error completing sale: ' . $e->getMessage());
        }
    }

    private function handleInventoryConflict($inventoryIssues)
    {
        $errorMessage = "❌ Insufficient inventory detected:\n\n";
        foreach ($inventoryIssues as $issue) {
            $errorMessage .= "• {$issue['product']}: Need {$issue['requested']}, Only {$issue['available']} available\n";
        }
        $errorMessage .= "\n🔄 Cart has been updated with current stock levels.";

        $this->error($errorMessage);
        $this->refreshCartInventory();
    }

    // Add this to your component to periodically check cart validity
    public function validateCartItems()
    {
        $hasChanges = false;

        foreach ($this->cartItems as $index => $item) {
            $currentInventory = Inventory::where('product_id', $item['product_id'])
                ->where('warehouse_id', $this->selectedWarehouse)
                ->first();

            $availableQty = $currentInventory ? $currentInventory->quantity_available : 0;

            if ($item['quantity'] > $availableQty) {
                $this->cartItems[$index]['quantity'] = max(0, $availableQty);
                $this->cartItems[$index]['subtotal'] = $this->cartItems[$index]['quantity'] * $this->cartItems[$index]['price'];
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            $this->cartItems = array_filter($this->cartItems, fn($item) => $item['quantity'] > 0);
            $this->cartItems = array_values($this->cartItems);
            $this->calculateTotals();
            $this->warning('Cart updated due to inventory changes by other users.');
        }
    }

    // Call this method periodically (e.g., every 30 seconds)

    /**
     * Refresh cart items with current inventory levels
     */
    private function refreshCartInventory()
    {
        foreach ($this->cartItems as $index => $item) {
            $currentInventory = Inventory::where('product_id', $item['product_id'])
                ->where('warehouse_id', $this->selectedWarehouse)
                ->first();

            $availableQty = $currentInventory ? $currentInventory->quantity_available : 0;

            // Update cart item quantity if it exceeds available stock
            if ($this->cartItems[$index]['quantity'] > $availableQty) {
                $this->cartItems[$index]['quantity'] = max(0, $availableQty);
                $this->cartItems[$index]['subtotal'] = $this->cartItems[$index]['quantity'] * $this->cartItems[$index]['price'];
            }
        }

        // Remove items with zero quantity
        $this->cartItems = array_filter($this->cartItems, fn($item) => $item['quantity'] > 0);
        $this->cartItems = array_values($this->cartItems); // Re-index array

        $this->calculateTotals();
    }

    public function resetSale()
    {
        $this->cartItems = [];
        $this->selectedCustomer = null;
        $this->discountAmount = 0;
        $this->paidAmount = 0;
        $this->saleNotes = '';
        $this->updateCartTotals();
    }

    public function scanBarcode($barcode)
    {
        if (!$this->checkActiveShift()) return;

        $product = Product::where('barcode', $barcode)
            ->where('status', 'active')
            ->first();

        if ($product) {
            $this->addToCart($product->id);
        } else {
            $this->error('Product not found with barcode: ' . $barcode);
        }
    }

    /**
     * Set quick cash amount for payment
     */
    public function setQuickCash($amount)
    {
        if (!$this->currentShift) {
            $this->addError('shift', 'No active shift found.');
            return;
        }

        if (count($this->cartItems) === 0) {
            $this->addError('cart', 'Cart is empty.');
            return;
        }

        $this->paidAmount = $amount;
        $this->paymentMethod = 'cash';

        // Auto-open payment modal if total is less than or equal to quick cash amount
        if ($this->totalAmount <= $amount) {
            $this->calculateChange();
            $this->showPaymentModal = true;
        } else {
            $this->error('Insufficient Payment', 'Set quick cash is less than the order`s total amount.');
        }
    }

    /**
     * Set exact cash amount (same as total)
     */
    public function setExactCash()
    {
        if (!$this->currentShift) {
            $this->addError('shift', 'No active shift found.');
            return;
        }

        if (count($this->cartItems) === 0) {
            $this->addError('cart', 'Cart is empty.');
            return;
        }

        $this->paidAmount = $this->totalAmount;
        $this->paymentMethod = 'cash';

        // Auto-open payment modal
        $this->showPaymentModal = true;
    }

    // ===== BARCODE SCANNING METHODS =====
    public function openBarcodeModal()
    {
        if (!$this->checkActiveShift()) return;

        $this->barcodeInput = '';
        $this->scannedItems = [];
        $this->showBarcodeModal = true;

        // Dispatch event to focus input (handled by JavaScript)
        $this->dispatch('barcode-modal-opened');
    }

    public function processBarcodeInput()
    {
        if (empty($this->barcodeInput)) {
            return;
        }

        $product = Product::where('barcode', $this->barcodeInput)
            ->where('status', 'active')
            ->with(['inventory' => function ($query) {
                $query->where('warehouse_id', $this->selectedWarehouse);
            }])
            ->first();

        if ($product) {
            $inventory = $product->inventory->first();
            $availableStock = $inventory ? $inventory->quantity_available : 0;

            if ($availableStock <= 0) {
                $this->error('Product out of stock: ' . $product->name);
                $this->barcodeInput = '';
                return;
            }

            // Check if item already scanned in this batch
            $existingIndex = null;
            foreach ($this->scannedItems as $index => $item) {
                if ($item['product_id'] == $product->id) {
                    $existingIndex = $index;
                    break;
                }
            }

            if ($existingIndex !== null) {
                if ($this->scannedItems[$existingIndex]['quantity'] >= $availableStock) {
                    $this->error('Cannot add more. Stock limit: ' . $availableStock);
                    $this->barcodeInput = '';
                    return;
                }
                $this->scannedItems[$existingIndex]['quantity']++;
                $this->scannedItems[$existingIndex]['subtotal'] =
                    $this->scannedItems[$existingIndex]['quantity'] * $this->scannedItems[$existingIndex]['price'];
            } else {
                // Add new item to scanned batch with default selling price
                $this->scannedItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->selling_price, // Uses selling price by default
                    'quantity' => 1,
                    'available_stock' => $availableStock,
                    'subtotal' => $product->selling_price,
                ];
            }

            $this->success('Scanned: ' . $product->name);
        } else {
            $this->error('Product not found: ' . $this->barcodeInput);
        }

        $this->barcodeInput = '';
    }

    public function addScannedItemsToCart()
    {
        if (empty($this->scannedItems)) {
            $this->error('No items scanned yet.');
            return;
        }

        $addedCount = 0;
        foreach ($this->scannedItems as $scannedItem) {
            $cartKey = $scannedItem['product_id'];

            if (isset($this->cartItems[$cartKey])) {
                // Update existing cart item
                $this->cartItems[$cartKey]['quantity'] += $scannedItem['quantity'];
                $this->cartItems[$cartKey]['subtotal'] =
                    $this->cartItems[$cartKey]['quantity'] * $this->cartItems[$cartKey]['price'];
            } else {
                // Add as new cart item
                $this->cartItems[$cartKey] = $scannedItem;
            }
            $addedCount++;
        }

        $this->updateCartTotals();
        $this->scannedItems = [];
        $this->showBarcodeModal = false;
        $this->success($addedCount . ' item(s) added to cart successfully!');
    }

    public function removeScannedItem($index)
    {
        if (isset($this->scannedItems[$index])) {
            $itemName = $this->scannedItems[$index]['name'];
            unset($this->scannedItems[$index]);
            $this->scannedItems = array_values($this->scannedItems); // Re-index array
            $this->success('Removed: ' . $itemName);
        }
    }

    public function updateScannedItemQuantity($index, $quantity)
    {
        if (isset($this->scannedItems[$index])) {
            if ($quantity <= 0) {
                $this->removeScannedItem($index);
                return;
            }

            $maxStock = $this->scannedItems[$index]['available_stock'];
            if ($quantity > $maxStock) {
                $this->error('Quantity exceeds available stock (' . $maxStock . ')');
                return;
            }

            $this->scannedItems[$index]['quantity'] = $quantity;
            $this->scannedItems[$index]['subtotal'] =
                $this->scannedItems[$index]['quantity'] * $this->scannedItems[$index]['price'];
        }
    }

    public function updatedBarcodeInput()
    {
        // Auto-process when barcode is entered (typical barcode length is 8-13 characters)
        if (strlen($this->barcodeInput) >= 8) {
            $this->processBarcodeInput();
        }
    }

    public function clearBarcodeInput()
    {
        $this->barcodeInput = '';
    }

    public function clearScannedItems()
    {
        $this->scannedItems = [];
        $this->success('Scanned items cleared.');
    }

    // ===== NEW CUSTOMER METHODS =====
    public function openCustomerModal()
    {
        $this->resetCustomerForm();
        $this->showCustomerModal = true;
    }

    public function createCustomer()
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerEmail' => 'nullable|email|unique:customers,email',
            'customerPhone' => 'nullable|string|max:20',
        ]);

        try {
            $customer = Customer::create([
                'name' => $this->customerName,
                'email' => $this->customerEmail,
                'phone' => $this->customerPhone,
                'address' => $this->customerAddress,
                'type' => 'individual',
                'is_active' => true,
            ]);

            $this->selectedCustomer = $customer->id;
            $this->showCustomerModal = false;
            $this->success('New customer created and selected: ' . $customer->name);
            $this->resetCustomerForm();
        } catch (\Exception $e) {
            $this->error('Error creating customer: ' . $e->getMessage());
        }
    }

    private function resetCustomerForm()
    {
        $this->customerName = '';
        $this->customerEmail = '';
        $this->customerPhone = '';
        $this->customerAddress = '';
    }

    // ===== CUSTOMER SEARCH METHODS =====
    public function openSearchCustomerModal()
    {
        $this->customerSearch = '';
        $this->customerSearchResults = [];
        $this->showSearchCustomerModal = true;
    }

    public function searchCustomers()
    {
        if (strlen($this->customerSearch) >= 2) {
            $this->customerSearchResults = Customer::where('is_active', true)
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('email', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('phone', 'like', '%' . $this->customerSearch . '%');
                })
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->customerSearchResults = [];
        }
    }

    public function selectSearchedCustomer($customerId)
    {
        $this->selectedCustomer = $customerId;
        $this->showSearchCustomerModal = false;
        $customer = Customer::find($customerId);
        $this->success('Customer selected: ' . $customer->name);
    }

    public function updatedCustomerSearch()
    {
        $this->searchCustomers();
    }

    // ===== DISCOUNT METHODS =====
    public function openDiscountModal()
    {
        if (empty($this->cartItems)) {
            $this->error('Cart is empty. Add items first.');
            return;
        }
        $this->discountType = 'percentage';
        $this->discountValue = '';
        $this->showDiscountModal = true;
    }

    public function removeDiscount()
    {
        $this->discountType = null;
        $this->discountValue = 0;
        $this->discountAmount = 0;
        $this->updateCartTotals();
        $this->success('Discount removed!');
    }

    // ===== HOLD SALE METHODS =====
    public function openHoldSaleModal()
    {
        if (!$this->checkActiveShift()) return;

        if (empty($this->cartItems)) {
            $this->error('Cart is empty. Add items first.');
            return;
        }
        $this->holdReference = 'HOLD-' . date('YmdHis');
        $this->holdNotes = '';
        $this->showHoldSaleModal = true;
    }

    public function holdSale()
    {
        if (!$this->checkActiveShift()) return;

        $this->validate([
            'holdReference' => 'required|string|max:255',
        ]);

        try {
            // Create a held sale record
            $heldSale = Sale::create([
                'invoice_number' => $this->holdReference,
                'customer_id' => $this->selectedCustomer,
                'warehouse_id' => $this->selectedWarehouse,
                'user_id' => auth()->id(),
                'shift_id' => $this->currentShift->id, // Associate with current shift
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->discountAmount,
                'tax_amount' => $this->taxAmount,
                'total_amount' => $this->totalAmount,
                'paid_amount' => 0,
                'change_amount' => 0,
                'payment_method' => 'cash',
                'status' => 'draft',
                'notes' => 'HELD SALE: ' . $this->holdNotes,
                'completed_at' => null,
            ]);

            // Create sale items
            foreach ($this->cartItems as $item) {
                $product = Product::find($item['product_id']);

                SaleItem::create([
                    'sale_id' => $heldSale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['subtotal'],
                    'cost_price' => $product->cost_price ?? 0, // Include cost price for profit calculation
                ]);
            }

            $this->success('Sale held successfully! Reference: ' . $this->holdReference);
            $this->resetSale();
            $this->showHoldSaleModal = false;
        } catch (\Exception $e) {
            $this->error('Error holding sale: ' . $e->getMessage());
        }
    }

    public function loadHeldSales()
    {
        $this->heldSales = Sale::where('status', 'draft')
            ->where('user_id', auth()->id()) // Only show current user's held sales
            ->with(['customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'customer_name' => $sale->customer?->name ?? 'Walk-in Customer',
                    'total_amount' => $sale->total_amount,
                    'items_count' => $sale->items->count(),
                    'created_at' => $sale->created_at->format('M d, Y H:i'),
                    'notes' => $sale->notes,
                ];
            })
            ->toArray();
    }

    public function openHeldSalesModal()
    {
        $this->loadHeldSales();
        $this->showHeldSalesModal = true;
    }

    public function retrieveHeldSale($saleId)
    {
        if (!$this->checkActiveShift()) return;

        try {
            $heldSale = Sale::with(['customer', 'items.product'])->find($saleId);

            if (!$heldSale || $heldSale->status !== 'draft') {
                $this->error('Held sale not found or already processed.');
                return;
            }

            // Clear current cart
            $this->resetSale();

            // Load held sale data
            $this->selectedCustomer = $heldSale->customer_id;
            $this->selectedWarehouse = $heldSale->warehouse_id;
            $this->discountAmount = $heldSale->discount_amount;
            $this->saleNotes = str_replace('HELD SALE: ', '', $heldSale->notes);

            // Load cart items from held sale
            foreach ($heldSale->items as $item) {
                $product = $item->product;
                if ($product) {
                    $inventory = $product->inventory()
                        ->where('warehouse_id', $this->selectedWarehouse)
                        ->first();

                    $availableStock = $inventory ? $inventory->quantity_available : 0;

                    $this->cartItems[$product->id] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'available_stock' => $availableStock,
                        'subtotal' => $item->total_price,
                    ];
                }
            }

            $this->updateCartTotals();

            // Delete the held sale record since we're resuming it
            $heldSale->delete();

            $this->showHeldSalesModal = false;
            $this->success('Held sale retrieved successfully! Reference: ' . $heldSale->invoice_number);
        } catch (\Exception $e) {
            $this->error('Error retrieving held sale: ' . $e->getMessage());
        }
    }

    public function deleteHeldSale($saleId)
    {
        try {
            $heldSale = Sale::find($saleId);

            if (!$heldSale || $heldSale->status !== 'draft') {
                $this->error('Held sale not found or already processed.');
                return;
            }

            $heldSale->delete();
            $this->loadHeldSales();
            $this->success('Held sale deleted successfully!');
        } catch (\Exception $e) {
            $this->error('Error deleting held sale: ' . $e->getMessage());
        }
    }
}
