<?php

namespace App\Livewire\Sales;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\Warehouse;
use Livewire\Component;
use Mary\Traits\Toast;

class PointOfSale extends Component
{
    use Toast;

    // Cart and sale data
    public array $cartItems = [];
    public float $subtotal = 0;
    public float $discountAmount = 0;
    public float $taxAmount = 0;
    public float $totalAmount = 0;
    public float $paidAmount = 0;
    public float $changeAmount = 0;

    // Form fields
    public string $searchProduct = '';
    public $selectedCustomer = null;
    public $selectedWarehouse = '';
    public string $paymentMethod = 'cash';
    public string $saleNotes = '';

    // UI state
    public bool $showCustomerModal = false;
    public bool $showPaymentModal = false;
    public bool $showDiscountModal = false;
    public bool $showHoldSaleModal = false;
    public bool $showSearchCustomerModal = false;
    public array $searchResults = [];

    // Customer form fields
    public string $customerName = '';
    public string $customerEmail = '';
    public string $customerPhone = '';
    public string $customerAddress = '';

    // Hold sale fields
    public string $holdReference = '';
    public string $holdNotes = '';

    // Discount fields
    public string $discountType = 'percentage'; // percentage or fixed
    public $discountValue = '';

    // Customer search
    public string $customerSearch = '';
    public array $customerSearchResults = [];

    // Barcode scanning
    public bool $showBarcodeModal = false;
    public string $barcodeInput = '';
    public array $scannedItems = [];

    // Tax rate (configurable)
    public float $taxRate = 0.12; // 12% VAT

    public bool $showHeldSalesModal = false;
    public array $heldSales = [];

    public function mount()
    {
        // Set default warehouse
        $this->selectedWarehouse = Warehouse::where('is_active', true)->first()?->id;

        $this->loadHeldSales();
    }

    public function render()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('livewire.sales.point-of-sale', [
            'warehouses' => $warehouses,
            'customers' => $customers,
        ])->layout('layouts.app', ['title' => 'Point of Sale']);
    }

    public function updatedSearchProduct()
    {
        if (strlen($this->searchProduct) >= 2) {
            $this->searchResults = Product::where('status', 'active')
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->searchProduct . '%')
                        ->orWhere('sku', 'like', '%' . $this->searchProduct . '%')
                        ->orWhere('barcode', 'like', '%' . $this->searchProduct . '%');
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

    public function addToCart($productId)
    {
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
        } else {
            $this->cartItems[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->selling_price,
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
        $this->updateCartTotals();
        $this->success('Cart cleared!');
    }

    public function updateCartTotals()
    {
        $this->subtotal = collect($this->cartItems)->sum('subtotal');
        $this->taxAmount = $this->subtotal * $this->taxRate;
        $this->totalAmount = $this->subtotal + $this->taxAmount - $this->discountAmount;
        $this->calculateChange();
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
        if ($this->paidAmount < $this->totalAmount) {
            $this->error('Insufficient payment amount.');
            return;
        }

        try {
            // Create sale record
            $sale = Sale::create([
                'customer_id' => $this->selectedCustomer,
                'warehouse_id' => $this->selectedWarehouse,
                'user_id' => auth()->id(),
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

            // Create sale items and update inventory
            foreach ($this->cartItems as $item) {
                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['subtotal'],
                ]);

                // Update inventory
                $inventory = Inventory::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $this->selectedWarehouse)
                    ->first();

                if ($inventory) {
                    $oldQuantity = $inventory->quantity_on_hand;
                    $newQuantity = $oldQuantity - $item['quantity'];

                    $inventory->update(['quantity_on_hand' => $newQuantity]);

                    // Create stock movement
                    $inventory->product->stockMovements()->create([
                        'warehouse_id' => $this->selectedWarehouse,
                        'type' => 'sale',
                        'quantity_before' => $oldQuantity,
                        'quantity_changed' => -$item['quantity'],
                        'quantity_after' => $newQuantity,
                        'unit_cost' => $inventory->average_cost,
                        'reference_id' => $sale->id,
                        'reference_type' => Sale::class,
                        'user_id' => auth()->id(),
                        'notes' => 'Sale: ' . $sale->invoice_number,
                    ]);
                }
            }

            // Update customer stats if customer selected
            if ($this->selectedCustomer) {
                $customer = Customer::find($this->selectedCustomer);
                $customer->increment('total_orders');
                $customer->increment('total_purchases', $this->totalAmount);
                $customer->update(['last_purchase_at' => now()]);
            }

            $this->success('Sale completed successfully! Invoice: ' . $sale->invoice_number);
            $this->resetSale();
            $this->showPaymentModal = false;
        } catch (\Exception $e) {
            $this->error('Error completing sale: ' . $e->getMessage());
        }
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
        $product = Product::where('barcode', $barcode)
            ->where('status', 'active')
            ->first();

        if ($product) {
            $this->addToCart($product->id);
        } else {
            $this->error('Product not found with barcode: ' . $barcode);
        }
    }

    // ===== BARCODE SCANNING METHODS =====
    public function openBarcodeModal()
    {
        $this->barcodeInput = '';
        $this->scannedItems = [];
        $this->showBarcodeModal = true;

        // Dispatch event to focus input (handled by JavaScript)
        $this->dispatch('barcode-modal-opened');
    }

    public function processBarcodeInput()
    {
        if (empty($this->barcodeInput)) {
            return; // Don't show error, just return for empty input
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
                // Check stock limit
                if ($this->scannedItems[$existingIndex]['quantity'] >= $availableStock) {
                    $this->error('Cannot add more. Stock limit: ' . $availableStock);
                    $this->barcodeInput = '';
                    return;
                }
                $this->scannedItems[$existingIndex]['quantity']++;
                $this->scannedItems[$existingIndex]['subtotal'] =
                    $this->scannedItems[$existingIndex]['quantity'] * $this->scannedItems[$existingIndex]['price'];
            } else {
                // Add new item to scanned batch
                $this->scannedItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->selling_price,
                    'quantity' => 1,
                    'available_stock' => $availableStock,
                    'subtotal' => $product->selling_price,
                ];
            }

            $this->success('Scanned: ' . $product->name);
        } else {
            $this->error('Product not found: ' . $this->barcodeInput);
        }

        // Clear input for next scan
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

    // ===== EXACT CASH METHOD =====
    public function setExactCash()
    {
        $this->paidAmount = $this->totalAmount;
        $this->calculateChange();
        $this->success('Payment amount set to exact total: ₱' . number_format($this->totalAmount, 2));
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

    public function removeDiscount()
    {
        $this->discountAmount = 0;
        $this->updateCartTotals();
        $this->success('Discount removed!');
    }

    // ===== HOLD SALE METHODS =====
    public function openHoldSaleModal()
    {
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
                SaleItem::create([
                    'sale_id' => $heldSale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['subtotal'],
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
                        'name' => $item->product_name,
                        'sku' => $item->product_sku,
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
