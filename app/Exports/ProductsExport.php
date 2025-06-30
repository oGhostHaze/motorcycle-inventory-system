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
            $product->category->name ?? '',
            $product->subcategory->name ?? '',
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
            'Category',
            'Subcategory',
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
            'E' => 18,  // Category
            'F' => 18,  // Subcategory
            'G' => 15,  // Cost Price
            'H' => 15,  // Selling Price
            'I' => 15,  // Wholesale Price
            'J' => 15,  // Alt Price 1
            'K' => 15,  // Alt Price 2
            'L' => 15,  // Alt Price 3
            'M' => 15,  // Warranty Months
            'N' => 15,  // Track Serial
            'O' => 15,  // Track Warranty
            'P' => 15,  // Min Stock Level
            'Q' => 15,  // Max Stock Level
            'R' => 15,  // Reorder Point
            'S' => 15,  // Reorder Quantity
            'T' => 15,  // Total Stock
            'U' => 12,  // Status
            'V' => 30,  // Internal Notes
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT, // Barcode as text
            'G' => NumberFormat::FORMAT_NUMBER_00, // Cost Price
            'H' => NumberFormat::FORMAT_NUMBER_00, // Selling Price
            'I' => NumberFormat::FORMAT_NUMBER_00, // Wholesale Price
            'J' => NumberFormat::FORMAT_NUMBER_00, // Alt Price 1
            'K' => NumberFormat::FORMAT_NUMBER_00, // Alt Price 2
            'L' => NumberFormat::FORMAT_NUMBER_00, // Alt Price 3
            'M' => NumberFormat::FORMAT_NUMBER, // Warranty Months
            'P' => NumberFormat::FORMAT_NUMBER, // Min Stock Level
            'Q' => NumberFormat::FORMAT_NUMBER, // Max Stock Level
            'R' => NumberFormat::FORMAT_NUMBER, // Reorder Point
            'S' => NumberFormat::FORMAT_NUMBER, // Reorder Quantity
            'T' => NumberFormat::FORMAT_NUMBER, // Total Stock
        ];
    }
}
