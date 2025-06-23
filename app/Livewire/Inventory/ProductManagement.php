<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ProductBrand;
use App\Models\Warehouse;
use App\Models\Inventory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

class ProductManagement extends Component
{
    use WithPagination;
    use WithFileUploads;
    use Toast;

    public bool $showModal = false;
    public bool $editMode = false;
    public ?Product $selectedProduct = null;

    // Form fields
    public $name = '';
    public $sku = '';
    public $barcode = '';
    public $description = '';
    public $category_id = null;
    public $subcategory_id = null;
    public $product_brand_id = null;
    public $part_number = '';
    public $oem_number = '';
    public $cost_price = 0.00;
    public $selling_price = 0.00;
    public $wholesale_price = null;

    public $warranty_months = 0;
    public $track_serial = false;
    public $track_warranty = false;
    public $min_stock_level = 0;
    public $max_stock_level = 0;
    public $reorder_point = 0;
    public $reorder_quantity = 0;
    public $status = 'active';
    public $internal_notes = '';
    public $productImage;
    public $alt_price1 = null;
    public $alt_price2 = null;
    public $alt_price3 = null;

    // Search and filters
    public $search = '';
    public $categoryFilter = '';
    public $brandFilter = '';
    public $statusFilter = '';
    public $stockFilter = '';

    public $showViewModal = false;

    // Inventory fields for new products
    public $warehouseStock = [];

    // For searchable dropdowns
    public $categoriesSearchable;
    public $subcategoriesSearchable;
    public $brandsSearchable;

    protected $rules = [
        'name' => 'required|string|max:255',
        'sku' => 'required|string|max:100|unique:products,sku',
        'category_id' => 'required|exists:categories,id',
        'cost_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'min_stock_level' => 'required|integer|min:0',
        'status' => 'required|in:active,inactive,discontinued',
        'productImage' => 'nullable|image|max:2048',
    ];

    public function mount()
    {
        $this->loadWarehouses();
        $this->loadSearchableOptions();
    }

    public function loadWarehouses()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        foreach ($warehouses as $warehouse) {
            $this->warehouseStock[$warehouse->id] = [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'quantity' => 0,
                'location' => '',
            ];
        }
    }

    public function loadSearchableOptions()
    {
        // Load categories for searchable dropdown
        $this->categoriesSearchable = Category::where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        // Load brands for searchable dropdown
        $this->brandsSearchable = ProductBrand::where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        // Load subcategories based on selected category
        $this->loadSubcategories();
    }

    public function updatedCategoryId()
    {
        // Reset subcategory when category changes
        $this->subcategory_id = null;
        $this->loadSubcategories();
    }

    public function loadSubcategories()
    {
        if ($this->category_id) {
            $this->subcategoriesSearchable = Subcategory::where('category_id', $this->category_id)
                ->where('is_active', true)
                ->withCount('products')
                ->orderBy('name')
                ->get();
        } else {
            $this->subcategoriesSearchable = collect();
        }
    }

    public function render()
    {
        $products = Product::with(['category', 'brand', 'inventory'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('sku', 'like', '%' . $this->search . '%')
                ->orWhere('barcode', 'like', '%' . $this->search . '%'))
            ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->brandFilter, fn($q) => $q->where('product_brand_id', $this->brandFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->stockFilter, function ($q) {
                switch ($this->stockFilter) {
                    case 'low':
                        return $q->whereHas('inventory', function ($query) {
                            $query->whereRaw('quantity_on_hand <= products.min_stock_level');
                        });
                    case 'out':
                        return $q->whereHas('inventory', function ($query) {
                            $query->where('quantity_on_hand', 0);
                        });
                    case 'in_stock':
                        return $q->whereHas('inventory', function ($query) {
                            $query->where('quantity_on_hand', '>', 0);
                        });
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = ProductBrand::where('is_active', true)->orderBy('name')->get();
        $subcategories = collect();

        if ($this->category_id) {
            $subcategories = Subcategory::where('category_id', $this->category_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        $filterOptions = [
            'categories' => $categories->map(fn($cat) => ['value' => $cat->id, 'label' => $cat->name]),
            'brands' => $brands->map(fn($brand) => ['value' => $brand->id, 'label' => $brand->name]),
            'statuses' => [
                ['value' => '', 'label' => 'All Status'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
                ['value' => 'discontinued', 'label' => 'Discontinued'],
            ],
            'stock' => [
                ['value' => '', 'label' => 'All Stock'],
                ['value' => 'in_stock', 'label' => 'In Stock'],
                ['value' => 'low', 'label' => 'Low Stock'],
                ['value' => 'out', 'label' => 'Out of Stock'],
            ]
        ];

        return view('livewire.inventory.product-management', [
            'products' => $products,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'brands' => $brands,
            'filterOptions' => $filterOptions,
        ]);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->selectedProduct = null;
        $this->showModal = true;
        $this->resetValidation();
        $this->loadWarehouses();
        $this->loadSearchableOptions();
    }

    public function editProduct(Product $product)
    {
        $this->selectedProduct = $product;
        $this->editMode = true;

        // Load product data
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->barcode = $product->barcode ?? '';
        $this->description = $product->description ?? '';
        $this->category_id = $product->category_id;
        $this->subcategory_id = $product->subcategory_id;
        $this->product_brand_id = $product->product_brand_id;
        $this->part_number = $product->part_number ?? '';
        $this->oem_number = $product->oem_number ?? '';
        $this->cost_price = $product->cost_price;
        $this->selling_price = $product->selling_price;
        $this->wholesale_price = $product->wholesale_price;
        $this->alt_price1 = $product->alt_price1;
        $this->alt_price2 = $product->alt_price2;
        $this->alt_price3 = $product->alt_price3;
        $this->warranty_months = $product->warranty_months;
        $this->track_serial = $product->track_serial;
        $this->track_warranty = $product->track_warranty;
        $this->min_stock_level = $product->min_stock_level;
        $this->max_stock_level = $product->max_stock_level;
        $this->reorder_point = $product->reorder_point;
        $this->reorder_quantity = $product->reorder_quantity;
        $this->status = $product->status;
        $this->internal_notes = $product->internal_notes ?? '';

        // Load current inventory levels
        foreach ($product->inventory as $inventory) {
            $this->warehouseStock[$inventory->warehouse_id] = [
                'warehouse_id' => $inventory->warehouse_id,
                'warehouse_name' => $inventory->warehouse->name,
                'quantity' => $inventory->quantity_on_hand,
                'location' => $inventory->location ?? '',
            ];
        }

        // Load searchable options and include selected items
        $this->loadSearchableOptionsForEdit();

        $this->showModal = true;
        $this->resetValidation();
    }

    private function loadSearchableOptionsForEdit()
    {
        // Ensure selected options are included in searchable lists
        $selectedCategory = $this->category_id ? Category::find($this->category_id) : null;
        $selectedBrand = $this->product_brand_id ? ProductBrand::find($this->product_brand_id) : null;

        $this->categoriesSearchable = Category::where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->when($selectedCategory, function ($collection) use ($selectedCategory) {
                return $collection->merge(collect([$selectedCategory]))->unique('id');
            });

        $this->brandsSearchable = ProductBrand::where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->when($selectedBrand, function ($collection) use ($selectedBrand) {
                return $collection->merge(collect([$selectedBrand]))->unique('id');
            });

        $this->loadSubcategories();
    }

    public function save()
    {
        if ($this->editMode) {
            $this->rules['sku'] = 'required|string|max:100|unique:products,sku,' . $this->selectedProduct->id;
        }

        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'sku' => $this->sku,
                'barcode' => $this->barcode,
                'description' => $this->description,
                'category_id' => $this->category_id,
                'subcategory_id' => $this->subcategory_id,
                'product_brand_id' => $this->product_brand_id,
                'part_number' => $this->part_number,
                'oem_number' => $this->oem_number,
                'cost_price' => $this->cost_price,
                'selling_price' => $this->selling_price,
                'wholesale_price' => $this->wholesale_price,
                'alt_price1' => $this->alt_price1,
                'alt_price2' => $this->alt_price2,
                'alt_price3' => $this->alt_price3,
                'warranty_months' => $this->warranty_months,
                'track_serial' => $this->track_serial,
                'track_warranty' => $this->track_warranty,
                'min_stock_level' => $this->min_stock_level,
                'max_stock_level' => $this->max_stock_level,
                'reorder_point' => $this->reorder_point,
                'reorder_quantity' => $this->reorder_quantity,
                'status' => $this->status,
                'internal_notes' => $this->internal_notes,
            ];

            // Handle image upload
            if ($this->productImage) {
                $imagePath = $this->productImage->store('products', 'public');
                $data['images'] = [$imagePath];
            }

            if ($this->editMode) {
                $this->selectedProduct->update($data);
                $product = $this->selectedProduct;
                $this->success('Product updated successfully!');
            } else {
                $product = Product::create($data);
                $this->success('Product created successfully!');
            }

            // Save inventory for each warehouse
            foreach ($this->warehouseStock as $warehouseId => $stock) {
                if (!empty($stock['quantity']) || $stock['quantity'] === 0) {
                    Inventory::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouseId,
                        ],
                        [
                            'quantity_on_hand' => $stock['quantity'],
                            'location' => $stock['location'],
                        ]
                    );
                }
            }

            $this->showModal = false;
        } catch (\Exception $e) {
            $this->error('Error saving product: ' . $e->getMessage());
        }
    }

    public function generateSku()
    {
        $this->sku = 'PRD-' . strtoupper(Str::random(8));
    }

    public function generateBarcode()
    {
        $this->barcode = str_pad(mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
    }

    public function viewProduct($productId)
    {
        $product = Product::with([
            'category',
            'subcategory',
            'brand',
            'inventory.warehouse',
            'stockMovements' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
            'supplierProducts.supplier'
        ])->find($productId);

        if (!$product) {
            $this->error('Product not found.');
            return;
        }

        $this->selectedProduct = $product;
        $this->showViewModal = true;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'categoryFilter', 'brandFilter', 'statusFilter', 'stockFilter']);
    }

    private function resetForm()
    {
        $this->reset([
            'name',
            'sku',
            'barcode',
            'description',
            'category_id',
            'subcategory_id',
            'product_brand_id',
            'part_number',
            'oem_number',
            'cost_price',
            'selling_price',
            'wholesale_price',
            'warranty_months',
            'track_serial',
            'track_warranty',
            'min_stock_level',
            'max_stock_level',
            'reorder_point',
            'reorder_quantity',
            'status',
            'internal_notes',
            'productImage'
        ]);
        $this->alt_price1 = null;
        $this->alt_price2 = null;
        $this->alt_price3 = null;
        $this->status = 'active';
        $this->loadWarehouses();
    }
}
