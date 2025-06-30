<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 12px;
            color: #666;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .invoice-details,
        .customer-details {
            width: 48%;
        }

        .invoice-details h3,
        .customer-details h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .items-table .qty,
        .items-table .price,
        .items-table .total {
            text-align: right;
        }

        .totals {
            width: 300px;
            margin-left: auto;
            margin-bottom: 30px;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }

        .totals .total-row {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            text-align: center;
            color: #666;
        }

        .notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
        }

        .notice strong {
            color: #856404;
        }

        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">{{ config('app.name') }}</div>
        <div class="company-details">
            Computer Parts Inventory Management System<br>
            Your Business Address Here<br>
            Phone: Your Phone Number | Email: your@email.com
        </div>
    </div>

    <div class="invoice-info">
        <div class="invoice-details">
            <h3>Invoice Details</h3>
            <p><strong>Invoice Number:</strong> {{ $sale->invoice_number }}</p>
            <p><strong>Date:</strong> {{ $sale->created_at->format('M d, Y H:i') }}</p>
            <p><strong>Warehouse:</strong> {{ $sale->warehouse->name }}</p>
            <p><strong>Cashier:</strong> {{ $sale->user->name }}</p>
            <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</p>
        </div>

        <div class="customer-details">
            <h3>Customer Details</h3>
            <p><strong>Name:</strong> {{ $sale->customer->name ?? 'Walk-in Customer' }}</p>
            @if ($sale->customer)
                <p><strong>Email:</strong> {{ $sale->customer->email ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $sale->customer->phone ?? 'N/A' }}</p>
                @if ($sale->customer->address)
                    <p><strong>Address:</strong> {{ $sale->customer->address }}</p>
                @endif
            @endif
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>SKU</th>
                <th class="qty">Qty</th>
                <th class="price">Unit Price</th>
                <th class="total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name }}</strong>
                    </td>
                    <td>{{ $item->product->sku }}</td>
                    <td class="qty">{{ $item->quantity }}</td>
                    <td class="price">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="total">₱{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td style="text-align: right;">₱{{ number_format($sale->subtotal_amount, 2) }}</td>
            </tr>
            @if ($sale->discount_amount > 0)
                <tr>
                    <td>Discount:</td>
                    <td style="text-align: right;">-₱{{ number_format($sale->discount_amount, 2) }}</td>
                </tr>
            @endif
            @if ($sale->tax_amount > 0)
                <tr>
                    <td>Tax:</td>
                    <td style="text-align: right;">₱{{ number_format($sale->tax_amount, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>Total:</td>
                <td style="text-align: right;">₱{{ number_format($sale->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Amount Paid:</td>
                <td style="text-align: right;">₱{{ number_format($sale->paid_amount, 2) }}</td>
            </tr>
            @if ($sale->change_amount > 0)
                <tr>
                    <td>Change:</td>
                    <td style="text-align: right;">₱{{ number_format($sale->change_amount, 2) }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="notice">
        <strong>Important Notice:</strong><br>
        This is a SALES INVOICE DRAFT - Not valid as official receipt.<br>
        Please issue BIR-printed receipt manually for legal compliance.
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Generated on {{ now()->format('M d, Y H:i') }}</p>
        <p>This document was generated by {{ config('app.name') }} Inventory Management System</p>
    </div>
</body>

</html>
