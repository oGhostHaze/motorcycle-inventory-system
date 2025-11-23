<?php

namespace App\Livewire\Inventory;

use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Warehouse;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Mary\Traits\Toast;

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
    public $category_id = null;
    public $subcategory_id = null;
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
    public $statusFilter = '';
    public $stockFilter = '';
    public $duplicateFilter = '';

    public $showViewModal = false;

    // Inventory fields for new products
    public $warehouseStock = [];

    // For searchable dropdowns
    public $categoriesSearchable;
    public $subcategoriesSearchable;

    public $showExportModal = false;
    public $showImportModal = false;
    public $importFile;
    public $importType = 'new'; // 'new' or 'update'
    public $importPreview = [];
    public $showImportPreview = false;

    public $availableLocations = [];

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
        $this->loadAvailableLocations();
    }

    public function updatingDuplicateFilter()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }


    public function updatingStatusFilter()
    {
        $this->resetPage();
    }


    public function updatingStockFilter()
    {
        $this->resetPage();
    }


    public function loadAvailableLocations()
    {
        $this->availableLocations = InventoryLocation::active()
            ->ordered()
            ->get(['id', 'code', 'name'])
            ->map(fn($loc) => [
                'id' => $loc->id,
                'label' => $loc->name
            ])
            ->toArray();
    }

    public function loadWarehouses()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        foreach ($warehouses as $warehouse) {
            $this->warehouseStock[$warehouse->id] = [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'quantity' => 0,
                'inventory_location_id' => null,
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
        $products = Product::with(['category', 'inventory'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('sku', 'like', '%' . $this->search . '%')
                ->orWhere('barcode', 'like', '%' . $this->search . '%'))
            ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
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
            })->when($this->duplicateFilter, function ($q) {
                switch ($this->duplicateFilter) {
                    case 'similar_names':
                        return $this->filterSimilarNames($q);
                    case 'duplicate_skus':
                        return $this->filterDuplicateSkus($q);
                    case 'duplicate_barcodes':
                        return $this->filterDuplicateBarcodes($q);
                    case 'similar_products':
                        return $this->filterSimilarProducts($q);
                }
            })
            ->orderBy('name', 'asc')
            ->paginate(20);

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $subcategories = collect();

        if ($this->category_id) {
            $subcategories = Subcategory::where('category_id', $this->category_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        $filterOptions = [
            'categories' => $categories->map(fn($cat) => ['value' => $cat->id, 'label' => $cat->name]),
            'statuses' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
                ['value' => 'discontinued', 'label' => 'Discontinued'],
            ],
            'stock' => [
                ['value' => 'in_stock', 'label' => 'In Stock'],
                ['value' => 'low', 'label' => 'Low Stock'],
                ['value' => 'out', 'label' => 'Out of Stock'],
            ],
            'duplicates' => [
                ['value' => 'similar_names', 'label' => 'Similar Names'],
                ['value' => 'duplicate_skus', 'label' => 'Duplicate SKUs'],
                ['value' => 'duplicate_barcodes', 'label' => 'Duplicate Barcodes'],
                ['value' => 'similar_products', 'label' => 'Potential Duplicates'],
            ]
        ];

        return view('livewire.inventory.product-management', [
            'products' => $products,
            'categories' => $categories,
            'subcategories' => $subcategories,
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
        $this->category_id = $product->category_id;
        $this->subcategory_id = $product->subcategory_id;
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
                'inventory_location_id' => $inventory->inventory_location_id,
            ];
        }

        // Load searchable options and include selected items
        $this->loadSearchableOptionsForEdit();

        $this->showModal = true;
        $this->resetValidation();
    }


    private function filterSimilarNames($query)
    {
        // Find products with similar names (fuzzy matching)
        $products = Product::select('name')
            ->whereRaw('LENGTH(name) > 3')
            ->get()
            ->groupBy(function ($product) {
                // Create a simplified version for comparison
                return $this->normalizeProductName($product->name);
            })
            ->filter(function ($group) {
                return $group->count() > 1;
            })
            ->flatten()
            ->pluck('name')
            ->toArray();

        return $query->whereIn('name', $products);
    }

    private function filterDuplicateSkus($query)
    {
        // Find products with duplicate SKUs (should not happen but good to check)
        $duplicateSkus = Product::select('sku')
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->groupBy('sku')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('sku')
            ->toArray();

        return $query->whereIn('sku', $duplicateSkus);
    }

    private function filterDuplicateBarcodes($query)
    {
        // Find products with duplicate barcodes
        $duplicateBarcodes = Product::select('barcode')
            ->whereNotNull('barcode')
            ->where('barcode', '!=', '')
            ->groupBy('barcode')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('barcode')
            ->toArray();

        return $query->whereIn('barcode', $duplicateBarcodes);
    }

    private function filterSimilarProducts($query)
    {
        // Find products that are potentially duplicates based on multiple criteria
        return $query->whereExists(function ($subQuery) {
            $subQuery->select('id')
                ->from('products as p2')
                ->where('p2.id', '!=', 'products.id')
                ->where('p2.category_id', '=', 'products.category_id')
                ->where(function ($nameQuery) {
                    $nameQuery->whereRaw('SOUNDEX(p2.name) = SOUNDEX(products.name)')
                        ->orWhereRaw('p2.name LIKE CONCAT("%", products.name, "%")')
                        ->orWhereRaw('products.name LIKE CONCAT("%", p2.name, "%")');
                });
        });
    }

    private function normalizeProductName($name)
    {
        // Remove common words and normalize for comparison
        $commonWords = ['and', 'or', 'the', 'a', 'an', 'for', 'with', 'without', 'set', 'kit'];

        $normalized = strtolower($name);
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);

        $words = explode(' ', $normalized);
        $words = array_filter($words, function ($word) use ($commonWords) {
            return !in_array($word, $commonWords) && strlen($word) > 2;
        });

        sort($words);
        return implode(' ', $words);
    }

    private function loadSearchableOptionsForEdit()
    {
        // Ensure selected options are included in searchable lists
        $selectedCategory = $this->category_id ? Category::find($this->category_id) : null;

        $this->categoriesSearchable = Category::where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->when($selectedCategory, function ($collection) use ($selectedCategory) {
                return $collection->merge(collect([$selectedCategory]))->unique('id');
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
                'category_id' => $this->category_id,
                'subcategory_id' => $this->subcategory_id,
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
                Inventory::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouseId,
                    ],
                    [
                        'quantity_on_hand' => $stock['quantity'],
                        'inventory_location_id' => $stock['inventory_location_id'],
                    ]
                );
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
        $this->reset(['search', 'categoryFilter', 'statusFilter', 'stockFilter']);
    }

    private function resetForm()
    {
        $this->reset([
            'name',
            'sku',
            'barcode',
            'category_id',
            'subcategory_id',
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
    public function exportProducts()
    {
        try {
            $products = Product::with(['category', 'subcategory', 'inventory.warehouse'])
                ->get()
                ->map(function ($product) {
                    $totalStock = $product->inventory->sum('quantity_on_hand');

                    return [
                        'ID' => $product->id,
                        'Name' => $product->name,
                        'SKU' => $product->sku,
                        'Barcode' => $product->barcode,
                        'Category' => $product->category->name ?? '',
                        'Subcategory' => $product->subcategory->name ?? '',
                        'Cost Price' => $product->cost_price,
                        'Selling Price' => $product->selling_price,
                        'Wholesale Price' => $product->wholesale_price,
                        'Alt Price 1' => $product->alt_price1,
                        'Alt Price 2' => $product->alt_price2,
                        'Alt Price 3' => $product->alt_price3,
                        'Warranty Months' => $product->warranty_months,
                        'Track Serial' => $product->track_serial ? 'Yes' : 'No',
                        'Track Warranty' => $product->track_warranty ? 'Yes' : 'No',
                        'Min Stock Level' => $product->min_stock_level,
                        'Max Stock Level' => $product->max_stock_level,
                        'Reorder Point' => $product->reorder_point,
                        'Reorder Quantity' => $product->reorder_quantity,
                        'Total Stock' => $totalStock,
                        'Status' => $product->status,
                        'Internal Notes' => $product->internal_notes,
                    ];
                });

            $filename = 'products-export-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

            return Excel::download(new ProductsExport($products), $filename);
        } catch (\Exception $e) {
            $this->error('Export failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $template = [
                [
                    'ID' => '', // Leave empty for new products
                    'Name' => 'Sample Product',
                    'SKU' => 'PRD-SAMPLE',
                    'Barcode' => '123456789012',
                    'Category' => 'Electronics',
                    'Subcategory' => 'Computers',
                    'Cost Price' => '100.00',
                    'Selling Price' => '150.00',
                    'Wholesale Price' => '130.00',
                    'Alt Price 1' => '140.00',
                    'Alt Price 2' => '145.00',
                    'Alt Price 3' => '148.00',
                    'Warranty Months' => '12',
                    'Track Serial' => 'No',
                    'Track Warranty' => 'Yes',
                    'Min Stock Level' => '10',
                    'Max Stock Level' => '100',
                    'Reorder Point' => '15',
                    'Reorder Quantity' => '50',
                    'Total Stock' => '0', // For reference only
                    'Status' => 'active',
                    'Internal Notes' => 'Sample internal notes',
                ]
            ];

            $filename = 'products-import-template.xlsx';

            return Excel::download(new ProductsExport(collect($template)), $filename);
        } catch (\Exception $e) {
            $this->error('Template download failed: ' . $e->getMessage());
        }
    }

    public function processImport()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new ProductsImport($this->importType === 'update');
            Excel::import($import, $this->importFile);

            $this->importPreview = $import->getPreviewData();
            $this->showImportPreview = true;

            $this->success('File processed successfully. Please review the preview below.');
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
        }
    }

    public function confirmImport()
    {
        try {
            $import = new ProductsImport($this->importType === 'update');
            Excel::import($import, $this->importFile);

            $results = $import->getResults();

            $this->success(
                'Import completed! ' .
                    'Created: ' . $results['created'] . ', ' .
                    'Updated: ' . $results['updated'] . ', ' .
                    'Errors: ' . $results['errors']
            );

            $this->resetImport();
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
        }
    }

    public function resetImport()
    {
        $this->reset(['importFile', 'importType', 'importPreview', 'showImportPreview']);
        $this->showImportModal = false;
    }
}
