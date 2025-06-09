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

    // Tax rate (configurable)
    public float $taxRate = 0.12; // 12% VAT

    public function mount()
    {
        // Set default warehouse
        $this->selectedWarehouse = Warehouse::where('is_active', true)->first()?->id;
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
        $this->showBarcodeModal = true;
        // Focus will be handled by JavaScript
    }

    public function processBarcodeInput()
    {
        if (empty($this->barcodeInput)) {
            $this->error('Please enter a barcode');
            return;
        }

        $this->scanBarcode($this->barcodeInput);
        $this->barcodeInput = '';
    }

    public function quickBarcodeScan($barcode)
    {
        // This method can be called directly from JavaScript
        $this->scanBarcode($barcode);
        $this->showBarcodeModal = false;
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
                'payment_method' => 'pending',
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
}
