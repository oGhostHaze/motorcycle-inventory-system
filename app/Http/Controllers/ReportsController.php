<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;

class ReportsController extends Controller
{
    public function exportSalesPdf(Request $request)
    {
        // Check permissions
        if (!auth()->user()->can('view_reports')) {
            abort(403, 'Unauthorized to export reports.');
        }

        try {
            $startDate = $request->get('startDate', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('endDate', now()->format('Y-m-d'));

            // Get the same data as the Livewire component
            $data = $this->getSalesReportData($startDate, $endDate);

            // Generate PDF
            $pdf = Pdf::loadView('reports.sales-pdf', $data);
            $pdf->setPaper('A4', 'landscape');

            $filename = 'sales-report-' . $startDate . '-to-' . $endDate . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Sales report PDF export error: ' . $e->getMessage());
            return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
        }
    }

    public function exportSalesExcel(Request $request)
    {
        // Check permissions
        if (!auth()->user()->can('view_reports')) {
            abort(403, 'Unauthorized to export reports.');
        }

        try {
            $startDate = $request->get('startDate', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('endDate', now()->format('Y-m-d'));

            $filename = 'sales-report-' . $startDate . '-to-' . $endDate . '.xlsx';

            return Excel::download(new SalesReportExport($startDate, $endDate), $filename);
        } catch (\Exception $e) {
            \Log::error('Sales report Excel export error: ' . $e->getMessage());
            return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
        }
    }

    private function getSalesReportData($startDate, $endDate)
    {
        $salesQuery = Sale::with(['customer', 'warehouse', 'user', 'items.product.category'])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('status', 'completed');

        $sales = $salesQuery->get();

        // Sales Summary
        $salesSummary = [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'average_sale' => $sales->count() > 0 ? $sales->avg('total_amount') : 0,
            'cash_sales' => $sales->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => $sales->where('payment_method', 'card')->sum('total_amount'),
            'gcash_sales' => $sales->where('payment_method', 'gcash')->sum('total_amount'),
            'bank_transfer_sales' => $sales->where('payment_method', 'bank_transfer')->sum('total_amount'),
            'total_items_sold' => $sales->sum(function ($sale) {
                return $sale->items->sum('quantity');
            }),
        ];

        // Profit Summary
        $totalProfit = 0;
        $totalCost = 0;
        $totalRevenue = 0;

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $cost = ($item->cost_price ?? $item->product->cost_price ?? 0) * $item->quantity;
                $revenue = $item->total_price;

                $totalCost += $cost;
                $totalRevenue += $revenue;
                $totalProfit += ($revenue - $cost);
            }
        }

        $profitSummary = [
            'total_profit' => $totalProfit,
            'total_cost' => $totalCost,
            'total_revenue' => $totalRevenue,
            'profit_margin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0,
        ];

        // Top Products
        $topProducts = SaleItem::query()
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'products.cost_price',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT sale_items.sale_id) as total_sales')
            ])
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('sales.status', 'completed')
            ->groupBy(['products.id', 'products.name', 'products.sku', 'products.cost_price'])
            ->orderBy('total_revenue', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                $profit = $item->total_revenue - ($item->cost_price * $item->total_quantity);
                $profitMargin = $item->total_revenue > 0 ? ($profit / $item->total_revenue) * 100 : 0;

                return [
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'quantity' => $item->total_quantity,
                    'revenue' => $item->total_revenue,
                    'profit' => $profit,
                    'profit_margin' => $profitMargin,
                    'total_sales' => $item->total_sales,
                ];
            })
            ->toArray();

        // Top Customers
        $topCustomers = $salesQuery->clone()
            ->select([
                'customers.name',
                'customers.email',
                'customers.phone',
                DB::raw('COUNT(sales.id) as total_orders'),
                DB::raw('SUM(sales.total_amount) as total_spent'),
                DB::raw('AVG(sales.total_amount) as avg_order_value')
            ])
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->groupBy(['customers.id', 'customers.name', 'customers.email', 'customers.phone'])
            ->orderBy('total_spent', 'desc')
            ->limit(15)
            ->get()
            ->toArray();

        // Payment Methods
        $paymentMethods = $salesQuery->clone()
            ->select([
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount')
            ])
            ->groupBy('payment_method')
            ->orderBy('total_amount', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => ucfirst(str_replace('_', ' ', $item->payment_method)),
                    'count' => $item->count,
                    'total_amount' => $item->total_amount,
                ];
            })
            ->toArray();

        // Sales Trends (Daily)
        $salesTrends = $salesQuery->clone()
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(total_amount) as total_amount')
            ])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->toArray();

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'salesSummary' => $salesSummary,
            'profitSummary' => $profitSummary,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'paymentMethods' => $paymentMethods,
            'salesTrends' => $salesTrends,
            'generated_at' => now(),
            'generated_by' => auth()->user()->name,
        ];
    }
}
