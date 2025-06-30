<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProductsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithColumnFormatting, WithMapping
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products;
    }

    public function map($product): array
    {
        // If it's already an array (from the export method), return as is but fix barcode
        if (is_array($product)) {
            // Format barcode as text to prevent scientific notation
            $product['Barcode'] = $product['Barcode'] ? "'" . $product['Barcode'] : '';
            return array_values($product);
        }

        // If it's a model instance, map the properties
        $totalStock = $product->inventory->sum('quantity_on_hand');

        return [
            $product->id,
            $product->name,
            $product->sku,
            $product->barcode ? "'" . $product->barcode : '', // Prefix with ' to force text format
            $product->description,
            $product->category->name ?? '',
            $product->subcategory->name ?? '',
            $product->brand->name ?? '',
            $product->part_number,
            $product->oem_number,
            $product->cost_price,
            $product->selling_price,
            $product->wholesale_price,
            $product->alt_price1,
            $product->alt_price2,
            $product->alt_price3,
            $product->warranty_months,
            $product->track_serial ? 'Yes' : 'No',
            $product->track_warranty ? 'Yes' : 'No',
            $product->min_stock_level,
            $product->max_stock_level,
            $product->reorder_point,
            $product->reorder_quantity,
            $totalStock,
            $product->status,
            $product->internal_notes,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'SKU',
            'Barcode',
            'Description',
            'Category',
            'Subcategory',
            'Brand',
            'Part Number',
            'OEM Number',
            'Cost Price',
            'Selling Price',
            'Wholesale Price',
            'Alt Price 1',
            'Alt Price 2',
            'Alt Price 3',
            'Warranty Months',
            'Track Serial',
            'Track Warranty',
            'Min Stock Level',
            'Max Stock Level',
            'Reorder Point',
            'Reorder Quantity',
            'Total Stock',
            'Status',
            'Internal Notes',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // All data styling
            "A1:{$lastColumn}{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFD1D5DB'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
            // Data rows alternating background
            "A2:{$lastColumn}{$lastRow}" => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF8F9FA']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 30,  // Name
            'C' => 18,  // SKU
            'D' => 18,  // Barcode
            'E' => 35,  // Description
            'F' => 18,  // Category
            'G' => 18,  // Subcategory
            'H' => 18,  // Brand
            'I' => 18,  // Part Number
            'J' => 18,  // OEM Number
            'K' => 15,  // Cost Price
            'L' => 15,  // Selling Price
            'M' => 15,  // Wholesale Price
            'N' => 15,  // Alt Price 1
            'O' => 15,  // Alt Price 2
            'P' => 15,  // Alt Price 3
            'Q' => 15,  // Warranty Months
            'R' => 15,  // Track Serial
            'S' => 15,  // Track Warranty
            'T' => 15,  // Min Stock Level
            'U' => 15,  // Max Stock Level
            'V' => 15,  // Reorder Point
            'W' => 15,  // Reorder Quantity
            'X' => 15,  // Total Stock
            'Y' => 12,  // Status
            'Z' => 30,  // Internal Notes
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT, // Barcode as text
            'K' => NumberFormat::FORMAT_NUMBER_00, // Cost Price
            'L' => NumberFormat::FORMAT_NUMBER_00, // Selling Price
            'M' => NumberFormat::FORMAT_NUMBER_00, // Wholesale Price
            'N' => NumberFormat::FORMAT_NUMBER_00, // Alt Price 1
            'O' => NumberFormat::FORMAT_NUMBER_00, // Alt Price 2
            'P' => NumberFormat::FORMAT_NUMBER_00, // Alt Price 3
            'Q' => NumberFormat::FORMAT_NUMBER, // Warranty Months
            'T' => NumberFormat::FORMAT_NUMBER, // Min Stock Level
            'U' => NumberFormat::FORMAT_NUMBER, // Max Stock Level
            'V' => NumberFormat::FORMAT_NUMBER, // Reorder Point
            'W' => NumberFormat::FORMAT_NUMBER, // Reorder Quantity
            'X' => NumberFormat::FORMAT_NUMBER, // Total Stock
        ];
    }
}
