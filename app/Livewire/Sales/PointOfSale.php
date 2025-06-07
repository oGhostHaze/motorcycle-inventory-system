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
    public array $searchResults = [];

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
}
