<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Encoding\Encoding;

class BarcodeService
{
    /**
     * Generate QR Code for product
     */
    public function generateQRCode(string $data, array $options = []): string
    {
        $qrCode = QrCode::create($data)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setSize($options['size'] ?? 300)
            ->setMargin($options['margin'] ?? 10)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        // Add logo if provided
        $logo = null;
        if (isset($options['logo_path']) && file_exists($options['logo_path'])) {
            $logo = Logo::create($options['logo_path'])
                ->setResizeToWidth(50);
        }

        // Add label if provided
        $label = null;
        if (isset($options['label'])) {
            $label = Label::create($options['label'])
                ->setTextColor(new Color(0, 0, 0));
        }

        $writer = new PngWriter();
        $result = $writer->write($qrCode, $logo, $label);

        return base64_encode($result->getString());
    }

    /**
     * Generate QR Code for product and save to storage
     */
    public function generateAndSaveQRCode(string $data, string $filename, array $options = []): string
    {
        $qrCode = QrCode::create($data)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setSize($options['size'] ?? 300)
            ->setMargin($options['margin'] ?? 10)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $path = 'qrcodes/' . $filename . '.png';
        \Storage::disk('public')->put($path, $result->getString());

        return $path;
    }

    /**
     * Generate barcode for product (Code 128)
     */
    public function generateBarcode(string $code): string
    {
        // Simple barcode generation using HTML/CSS
        // For more advanced barcodes, consider using picqer/php-barcode-generator
        $barcode = '';
        $bars = $this->generateCode128Bars($code);

        foreach ($bars as $bar) {
            $width = $bar['width'];
            $color = $bar['black'] ? '#000000' : '#FFFFFF';
            $barcode .= "<div style='display:inline-block;width:{$width}px;height:50px;background-color:{$color}'></div>";
        }

        return $barcode;
    }

    /**
     * Generate product barcode with proper formatting
     */
    public function generateProductBarcode(string $sku): string
    {
        // Add prefix for business
        $prefix = config('app.barcode_prefix', 'MPS');
        $fullCode = $prefix . str_pad($sku, 8, '0', STR_PAD_LEFT);

        return $this->generateBarcode($fullCode);
    }

    /**
     * Generate QR code data for product
     */
    public function generateProductQRData(\App\Models\Product $product): string
    {
        return json_encode([
            'type' => 'product',
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'price' => $product->selling_price,
            'barcode' => $product->barcode,
            'url' => route('products.show', $product->id)
        ]);
    }

    /**
     * Simple Code 128 bar generation (basic implementation)
     */
    private function generateCode128Bars(string $code): array
    {
        // This is a simplified version
        // For production, use a proper barcode library like picqer/php-barcode-generator
        $bars = [];

        foreach (str_split($code) as $char) {
            $ascii = ord($char);
            $pattern = $ascii % 4; // Simplified pattern

            switch ($pattern) {
                case 0:
                    $bars[] = ['width' => 2, 'black' => true];
                    $bars[] = ['width' => 2, 'black' => false];
                    break;
                case 1:
                    $bars[] = ['width' => 3, 'black' => true];
                    $bars[] = ['width' => 1, 'black' => false];
                    break;
                case 2:
                    $bars[] = ['width' => 1, 'black' => true];
                    $bars[] = ['width' => 3, 'black' => false];
                    break;
                case 3:
                    $bars[] = ['width' => 4, 'black' => true];
                    $bars[] = ['width' => 1, 'black' => false];
                    break;
            }
        }

        return $bars;
    }

    /**
     * Validate and decode QR code data
     */
    public function decodeProductQR(string $qrData): ?array
    {
        try {
            $data = json_decode($qrData, true);

            if (!isset($data['type']) || $data['type'] !== 'product') {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }
}
