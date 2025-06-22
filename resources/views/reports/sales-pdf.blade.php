<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #1f2937;
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0;
            color: #6b7280;
        }

        .section {
            margin-bottom: 25px;
            break-inside: avoid;
        }

        .section-title {
            background-color: #f3f4f6;
            padding: 8px 12px;
            font-weight: bold;
            color: #1f2937;
            border-left: 4px solid #3b82f6;
            margin-bottom: 15px;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stats-row {
            display: table-row;
        }

        .stat-item {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
            display: block;
        }

        .stat-label {
            font-size: 11px;
            color: #6b7280;
            margin-top: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px 6px;
            text-align: left;
        }

        th {
            background-color: #f9fafb;
            font-weight: bold;
            color: #374151;
            font-size: 11px;
        }

        td {
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .profit-positive {
            color: #059669;
        }

        .profit-negative {
            color: #dc2626;
        }

        .currency::before {
            content: '₱';
        }

        .percentage::after {
            content: '%';
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        .page-break {
            page-break-before: always;
        }

        .two-column {
            display: table;
            width: 100%;
        }

        .column {
            display: table-cell;
            width: 50%;
            padding-right: 15px;
            vertical-align: top;
        }

        .column:last-child {
            padding-right: 0;
            padding-left: 15px;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="header">
        <h1>Sales Report</h1>
        <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}</p>
        <p><strong>Generated:</strong> {{ $generated_at->format('M j, Y g:i A') }} by {{ $generated_by }}</p>
    </div>

    {{-- Sales Summary --}}
    <div class="section">
        <div class="section-title">Sales Summary</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stat-item">
                    <span class="stat-value">{{ number_format($salesSummary['total_sales']) }}</span>
                    <div class="stat-label">Total Sales</div>
                </div>
                <div class="stat-item">
                    <span class="stat-value currency">{{ number_format($salesSummary['total_revenue'], 2) }}</span>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-item">
                    <span class="stat-value currency">{{ number_format($salesSummary['average_sale'], 2) }}</span>
                    <div class="stat-label">Average Sale</div>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ number_format($salesSummary['total_items_sold']) }}</span>
                    <div class="stat-label">Items Sold</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Profit Summary --}}
    <div class="section">
        <div class="section-title">Profit Analysis</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stat-item">
                    <span
                        class="stat-value currency profit-positive">{{ number_format($profitSummary['total_profit'], 2) }}</span>
                    <div class="stat-label">Total Profit</div>
                </div>
                <div class="stat-item">
                    <span class="stat-value currency">{{ number_format($profitSummary['total_cost'], 2) }}</span>
                    <div class="stat-label">Cost of Goods</div>
                </div>
                <div class="stat-item">
                    <span class="stat-value percentage">{{ number_format($profitSummary['profit_margin'], 1) }}</span>
                    <div class="stat-label">Profit Margin</div>
                </div>
                <div class="stat-item">
                    <span class="stat-value currency">{{ number_format($profitSummary['total_revenue'], 2) }}</span>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Methods --}}
    @if (!empty($paymentMethods))
        <div class="section">
            <div class="section-title">Payment Methods Breakdown</div>
            <table>
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th class="text-center">Count</th>
                        <th class="text-right">Total Amount</th>
                        <th class="text-right">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalPayments = collect($paymentMethods)->sum('total_amount'); @endphp
                    @foreach ($paymentMethods as $method)
                        @php $percentage = $totalPayments > 0 ? ($method['total_amount'] / $totalPayments) * 100 : 0; @endphp
                        <tr>
                            <td>{{ $method['method'] }}</td>
                            <td class="text-center">{{ number_format($method['count']) }}</td>
                            <td class="text-right">₱{{ number_format($method['total_amount'], 2) }}</td>
                            <td class="text-right">{{ number_format($percentage, 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Page Break --}}
    <div class="page-break"></div>

    {{-- Top Products --}}
    @if (!empty($topProducts))
        <div class="section">
            <div class="section-title">Top Performing Products</div>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th class="text-center">Qty Sold</th>
                        <th class="text-right">Revenue</th>
                        <th class="text-right">Profit</th>
                        <th class="text-right">Margin %</th>
                        <th class="text-center">Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (array_slice($topProducts, 0, 15) as $product)
                        <tr>
                            <td>{{ $product['name'] }}</td>
                            <td>{{ $product['sku'] }}</td>
                            <td class="text-center">{{ number_format($product['quantity']) }}</td>
                            <td class="text-right">₱{{ number_format($product['revenue'], 2) }}</td>
                            <td
                                class="text-right {{ $product['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                ₱{{ number_format($product['profit'], 2) }}
                            </td>
                            <td
                                class="text-right {{ $product['profit_margin'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                {{ number_format($product['profit_margin'], 1) }}%
                            </td>
                            <td class="text-center">{{ $product['total_sales'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Top Customers --}}
    @if (!empty($topCustomers))
        <div class="section">
            <div class="section-title">Top Customers</div>
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th class="text-center">Orders</th>
                        <th class="text-right">Total Spent</th>
                        <th class="text-right">Avg Order</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (array_slice($topCustomers, 0, 12) as $customer)
                        <tr>
                            <td>{{ $customer['name'] }}</td>
                            <td>{{ $customer['email'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $customer['total_orders'] }}</td>
                            <td class="text-right">₱{{ number_format($customer['total_spent'], 2) }}</td>
                            <td class="text-right">₱{{ number_format($customer['avg_order_value'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Sales Trends --}}
    @if (!empty($salesTrends))
        <div class="section">
            <div class="section-title">Daily Sales Trends</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-center">Sales Count</th>
                        <th class="text-right">Revenue</th>
                        <th class="text-right">Avg Sale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesTrends as $trend)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($trend['date'])->format('M j, Y') }}</td>
                            <td class="text-center">{{ $trend['sales_count'] }}</td>
                            <td class="text-right">₱{{ number_format($trend['total_amount'], 2) }}</td>
                            <td class="text-right">
                                ₱{{ number_format($trend['sales_count'] > 0 ? $trend['total_amount'] / $trend['sales_count'] : 0, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>This report was generated by {{ config('app.name') }} - Motorcycle Parts Inventory Management System</p>
        <p>Generated on {{ $generated_at->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>

</html>
