<?php

namespace App\Livewire\Purchasing;

use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class SupplierManagement extends Component
{
    use WithPagination;
    use Toast;

    public $showModal = false;
    public $showProductsModal = false;
    public $showOrdersModal = false;
    public $editMode = false;
    public $selectedSupplier = null;

    // Form fields
    public $name = '';
    public $contact_person = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $city = '';
    public $country = '';
    public $rating = '';
    public $lead_time_days = '';
    public $notes = '';
    public $is_active = true;

    // Product association
    public $supplierProducts = [];
    public $selectedProduct = '';
    public $supplier_sku = '';
    public $supplier_part_number = '';
    public $supplier_price = '';
    public $minimum_order_quantity = '';
    public $product_lead_time_days = '';
    public $is_preferred = false;

    // Search and filters
    public $search = '';
    public $countryFilter = '';
    public $statusFilter = '';
    public $ratingFilter = '';

    // Orders modal data
    public $supplierOrders = [];
    public $ordersSearch = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'contact_person' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:500',
        'city' => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
        'rating' => 'nullable|numeric|min:1|max:5',
        'lead_time_days' => 'nullable|integer|min:1',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $suppliers = Supplier::withCount(['purchaseOrders'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%'))
            ->when($this->countryFilter, fn($q) => $q->where('country', $this->countryFilter))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter))
            ->when($this->ratingFilter, fn($q) => $q->where('rating', '>=', $this->ratingFilter))
            ->orderBy('name')
            ->paginate(12);

        $countries = Supplier::distinct()->pluck('country')->filter()->sort()->values();
        $products = Product::where('status', 'active')->orderBy('name')->get();

        return view('livewire.purchasing.supplier-management', [
            'suppliers' => $suppliers,
            'countries' => $countries,
            'products' => $products,
        ])->layout('layouts.app', ['title' => 'Supplier Management']);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->selectedSupplier = null;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function editSupplier(Supplier $supplier)
    {
        $this->selectedSupplier = $supplier;
        $this->name = $supplier->name;
        $this->contact_person = $supplier->contact_person ?? '';
        $this->email = $supplier->email ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->address = $supplier->address ?? '';
        $this->city = $supplier->city ?? '';
        $this->country = $supplier->country ?? '';
        $this->rating = $supplier->rating;
        $this->lead_time_days = $supplier->lead_time_days;
        $this->notes = $supplier->notes ?? '';
        $this->is_active = $supplier->is_active;
        $this->editMode = true;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'contact_person' => $this->contact_person,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'city' => $this->city,
                'country' => $this->country,
                'rating' => $this->rating,
                'lead_time_days' => $this->lead_time_days,
                'notes' => $this->notes,
                'is_active' => $this->is_active,
            ];

            if ($this->editMode) {
                $this->selectedSupplier->update($data);
                $this->success('Supplier updated successfully!');
            } else {
                Supplier::create($data);
                $this->success('Supplier created successfully!');
            }

            $this->showModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->error('Error saving supplier: ' . $e->getMessage());
        }
    }

    // ========== FIXED METHODS FOR CREATE PO AND VIEW ORDERS ==========

    /**
     * Create Purchase Order - Redirect to PO management with supplier pre-selected
     * Using Livewire's redirect with query parameters for better reliability
     */
    public function createPurchaseOrder($supplierId)
    {
        $supplier = Supplier::find($supplierId);

        if (!$supplier) {
            $this->error('Supplier not found.');
            return;
        }

        // Use query parameters instead of session for more reliable data passing
        return redirect()->route('purchasing.purchase-orders', [
            'create_po' => 'true',
            'supplier_id' => $supplier->id,
            'supplier_name' => urlencode($supplier->name)
        ]);
    }

    /**
     * View Orders - Show modal with supplier's purchase orders
     * Fixed to properly load and display orders
     */
    public function viewOrders($supplierId)
    {
        $supplier = Supplier::find($supplierId);

        if (!$supplier) {
            $this->error('Supplier not found.');
            return;
        }

        $this->selectedSupplier = $supplier;
        $this->ordersSearch = ''; // Reset search
        $this->loadSupplierOrders();
        $this->showOrdersModal = true;
    }

    /**
     * Load purchase orders for selected supplier
     * Enhanced with better error handling
     */
    private function loadSupplierOrders()
    {
        if (!$this->selectedSupplier) return;

        try {
            $query = PurchaseOrder::with(['warehouse', 'requestedBy', 'items.product'])
                ->where('supplier_id', $this->selectedSupplier->id);

            if ($this->ordersSearch) {
                $query->where('po_number', 'like', '%' . $this->ordersSearch . '%');
            }

            $this->supplierOrders = $query->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            $this->error('Error loading orders: ' . $e->getMessage());
            $this->supplierOrders = collect();
        }
    }

    /**
     * Update orders search - Fixed to properly trigger reload
     */
    public function updatedOrdersSearch()
    {
        $this->loadSupplierOrders();
    }

    /**
     * Go to specific purchase order - Fixed to work from modal
     */
    public function goToPurchaseOrder($purchaseOrderId)
    {
        // Close the modal first
        $this->showOrdersModal = false;

        // Redirect with the PO ID
        return redirect()->route('purchasing.purchase-orders', [
            'poId' => $purchaseOrderId
        ]);
    }

    // ========== EXISTING METHODS ==========

    public function deleteSupplier(Supplier $supplier)
    {
        try {
            if ($supplier->purchaseOrders()->exists()) {
                $this->error('Cannot delete supplier with existing purchase orders.');
                return;
            }

            $supplier->delete();
            $this->success('Supplier deleted successfully!');
        } catch (\Exception $e) {
            $this->error('Error deleting supplier: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);
        $status = $supplier->is_active ? 'activated' : 'deactivated';
        $this->success("Supplier {$status} successfully!");
    }

    public function openProductsModal(Supplier $supplier)
    {
        $this->selectedSupplier = $supplier;
        $this->supplierProducts = $supplier->supplierProducts()
            ->with('product')
            ->get()
            ->toArray();
        $this->resetProductForm();
        $this->showProductsModal = true;
    }

    public function addProduct()
    {
        $this->validate([
            'selectedProduct' => 'required|exists:products,id',
            'supplier_sku' => 'nullable|string|max:100',
            'supplier_part_number' => 'nullable|string|max:100',
            'supplier_price' => 'required|numeric|min:0',
            'minimum_order_quantity' => 'nullable|integer|min:1',
            'product_lead_time_days' => 'nullable|integer|min:1',
        ]);

        try {
            // Check if product already exists for this supplier
            $existing = SupplierProduct::where('supplier_id', $this->selectedSupplier->id)
                ->where('product_id', $this->selectedProduct)
                ->first();

            if ($existing) {
                $this->error('This product is already associated with this supplier.');
                return;
            }

            SupplierProduct::create([
                'supplier_id' => $this->selectedSupplier->id,
                'product_id' => $this->selectedProduct,
                'supplier_sku' => $this->supplier_sku,
                'supplier_part_number' => $this->supplier_part_number,
                'supplier_price' => $this->supplier_price,
                'minimum_order_quantity' => $this->minimum_order_quantity,
                'lead_time_days' => $this->product_lead_time_days ?: $this->selectedSupplier->lead_time_days,
                'is_preferred' => $this->is_preferred,
                'is_active' => true,
            ]);

            // Refresh supplier products
            $this->supplierProducts = $this->selectedSupplier->supplierProducts()
                ->with('product')
                ->get()
                ->toArray();

            $this->resetProductForm();
            $this->success('Product added to supplier successfully!');
        } catch (\Exception $e) {
            $this->error('Error adding product: ' . $e->getMessage());
        }
    }

    public function removeSupplierProduct($supplierProductId)
    {
        try {
            SupplierProduct::find($supplierProductId)->delete();

            // Refresh supplier products
            $this->supplierProducts = $this->selectedSupplier->supplierProducts()
                ->with('product')
                ->get()
                ->toArray();

            $this->success('Product removed from supplier successfully!');
        } catch (\Exception $e) {
            $this->error('Error removing product: ' . $e->getMessage());
        }
    }

    public function togglePreferred($supplierProductId)
    {
        try {
            $supplierProduct = SupplierProduct::find($supplierProductId);

            if ($supplierProduct->is_preferred) {
                // Unset as preferred
                $supplierProduct->update(['is_preferred' => false]);
            } else {
                // Set as preferred and unset others for this product
                SupplierProduct::where('product_id', $supplierProduct->product_id)
                    ->where('id', '!=', $supplierProductId)
                    ->update(['is_preferred' => false]);

                $supplierProduct->update(['is_preferred' => true]);
            }

            // Refresh supplier products
            $this->supplierProducts = $this->selectedSupplier->supplierProducts()
                ->with('product')
                ->get()
                ->toArray();

            $this->success('Preferred supplier updated successfully!');
        } catch (\Exception $e) {
            $this->error('Error updating preferred supplier: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'countryFilter', 'statusFilter', 'ratingFilter']);
    }

    private function resetForm()
    {
        $this->reset([
            'name',
            'contact_person',
            'email',
            'phone',
            'address',
            'city',
            'country',
            'rating',
            'lead_time_days',
            'notes',
            'is_active'
        ]);
        $this->is_active = true;
    }

    private function resetProductForm()
    {
        $this->reset([
            'selectedProduct',
            'supplier_sku',
            'supplier_part_number',
            'supplier_price',
            'minimum_order_quantity',
            'product_lead_time_days',
            'is_preferred'
        ]);
    }

    public function getRatingStars($rating)
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $rating ? '★' : '☆';
        }
        return $stars;
    }
}
