<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarcodeScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'barcode',
        'scan_type',
        'device_type',
        'was_successful',
        'notes'
    ];

    protected $casts = [
        'was_successful' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
