<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ProductBrand;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $updateMode;
    protected $previewData = [];
    protected $results = [
        'created' => 0,
        'updated' => 0,
        'errors' => 0,
        'error_details' => []
    ];

    public function __construct($updateMode = false)
    {
        $this->updateMode = $updateMode;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Clean and prepare data
                $data = $this->prepareProductData($row);

                if ($this->updateMode) {
                    $this->updateProduct($data, $index);
                } else {
                    $this->createProduct($data, $index);
                }
            } catch (\Exception $e) {
                $this->results['errors']++;
                $this->results['error_details'][] = [
                    'row' => $index + 2, // +2 because of header and 0-index
                    'error' => $e->getMessage(),
                    'data' => $row->toArray()
                ];
            }
        }
    }

    protected function prepareProductData($row)
    {
        // Find or create category
        $category = null;
        if (!empty($row['category'])) {
            $category = Category::firstOrCreate(
                ['name' => trim($row['category'])],
                [
                    'slug' => Str::slug($row['category']),
                    'is_active' => true
                ]
            );
        }

        // Find or create subcategory
        $subcategory = null;
        if (!empty($row['subcategory']) && $category) {
            $subcategory = Subcategory::firstOrCreate(
                [
                    'name' => trim($row['subcategory']),
                    'category_id' => $category->id
                ],
                [
                    'slug' => Str::slug($row['subcategory']),
                    'is_active' => true
                ]
            );
        }

        // Find or create brand
        $brand = null;
        if (!empty($row['brand'])) {
            $brand = ProductBrand::firstOrCreate(
                ['name' => trim($row['brand'])],
                ['is_active' => true]
            );
        }

        return [
            'name' => trim($row['name']),
            'sku' => trim($row['sku']) ?: 'PRD-' . strtoupper(Str::random(8)),
            'barcode' => $row['barcode'] ?? null,
            'description' => $row['description'] ?? null,
            'category_id' => $category?->id,
            'subcategory_id' => $subcategory?->id,
            'product_brand_id' => $brand?->id,
            'part_number' => $row['part_number'] ?? null,
            'oem_number' => $row['oem_number'] ?? null,
            'cost_price' => $this->parseDecimal($row['cost_price']),
            'selling_price' => $this->parseDecimal($row['selling_price']),
            'wholesale_price' => $this->parseDecimal($row['wholesale_price']),
            'alt_price1' => $this->parseDecimal($row['alt_price_1']),
            'alt_price2' => $this->parseDecimal($row['alt_price_2']),
            'alt_price3' => $this->parseDecimal($row['alt_price_3']),
            'warranty_months' => $this->parseInteger($row['warranty_months']),
            'track_serial' => $this->parseBoolean($row['track_serial']),
            'track_warranty' => $this->parseBoolean($row['track_warranty']),
            'min_stock_level' => $this->parseInteger($row['min_stock_level']),
            'max_stock_level' => $this->parseInteger($row['max_stock_level']),
            'reorder_point' => $this->parseInteger($row['reorder_point']),
            'reorder_quantity' => $this->parseInteger($row['reorder_quantity']),
            'status' => in_array($row['status'], ['active', 'inactive', 'discontinued']) ? $row['status'] : 'active',
            'internal_notes' => $row['internal_notes'] ?? null,
            'slug' => Str::slug($row['name']),
        ];
    }

    protected function createProduct($data, $index)
    {
        // Check if SKU already exists
        if (Product::where('sku', $data['sku'])->exists()) {
            throw new \Exception("SKU '{$data['sku']}' already exists");
        }

        Product::create($data);
        $this->results['created']++;

        $this->previewData[] = [
            'action' => 'CREATE',
            'name' => $data['name'],
            'sku' => $data['sku'],
            'status' => 'Success'
        ];
    }

    protected function updateProduct($data, $index)
    {
        $product = null;

        // Try to find by SKU first, then by name
        if (!empty($data['sku'])) {
            $product = Product::where('sku', $data['sku'])->first();
        }

        if (!$product && !empty($data['name'])) {
            $product = Product::where('name', $data['name'])->first();
        }

        if ($product) {
            $product->update($data);
            $this->results['updated']++;

            $this->previewData[] = [
                'action' => 'UPDATE',
                'name' => $data['name'],
                'sku' => $data['sku'],
                'status' => 'Success'
            ];
        } else {
            // Create new product if not found in update mode
            Product::create($data);
            $this->results['created']++;

            $this->previewData[] = [
                'action' => 'CREATE',
                'name' => $data['name'],
                'sku' => $data['sku'],
                'status' => 'Success (New)'
            ];
        }
    }

    protected function parseDecimal($value)
    {
        if (empty($value)) return null;
        return (float) str_replace(',', '', $value);
    }

    protected function parseInteger($value)
    {
        if (empty($value)) return null;
        return (int) $value;
    }

    protected function parseBoolean($value)
    {
        if (empty($value)) return false;
        return in_array(strtolower($value), ['yes', 'true', '1', 'on']);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Product name is required',
            'selling_price.required' => 'Selling price is required',
            'selling_price.numeric' => 'Selling price must be a number',
        ];
    }

    public function getPreviewData()
    {
        return $this->previewData;
    }

    public function getResults()
    {
        return $this->results;
    }
}
