<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;

class AdvancedBarcodeService
{
    // Uncomment and install: composer require picqer/php-barcode-generator

    protected $htmlGenerator;
    protected $pngGenerator;

    public function __construct()
    {
        $this->htmlGenerator = new BarcodeGeneratorHTML();
        $this->pngGenerator = new BarcodeGeneratorPNG();
    }

    public function generateHTMLBarcode(string $code, string $type = 'C128'): string
    {
        return $this->htmlGenerator->getBarcode($code, $type);
    }

    public function generatePNGBarcode(string $code, string $type = 'C128'): string
    {
        return base64_encode($this->pngGenerator->getBarcode($code, $type));
    }
}
