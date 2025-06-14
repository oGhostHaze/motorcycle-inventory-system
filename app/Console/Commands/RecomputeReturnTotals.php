<?php
// Create this as: app/Console/Commands/RecomputeReturnTotals.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SalesShift;
use App\Models\SaleReturn;
use App\Models\SaleItem;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class RecomputeReturnTotals extends Command
{
    protected $signature = 'returns:recompute
                            {--shifts : Recompute shift totals}
                            {--items : Recompute sale item returned quantities}
                            {--all : Recompute everything}
                            {--dry-run : Show what would be changed without making changes}';

    protected $description = 'Recompute return totals and fix incorrect calculations';

    public function handle()
    {
        $this->info('🔄 Starting Return Totals Recomputation...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $recomputeShifts = $this->option('shifts') || $this->option('all');
        $recomputeItems = $this->option('items') || $this->option('all');

        if (!$recomputeShifts && !$recomputeItems) {
            $this->error('Please specify what to recompute: --shifts, --items, or --all');
            return 1;
        }

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $totalFixed = 0;

        if ($recomputeItems) {
            $totalFixed += $this->recomputeSaleItemReturnedQuantities($dryRun);
        }

        if ($recomputeShifts) {
            $totalFixed += $this->recomputeShiftTotals($dryRun);
        }

        $this->newLine();
        $this->info("✅ Recomputation completed!");
        $this->info("📊 Total records that " . ($dryRun ? 'would be' : 'were') . " updated: {$totalFixed}");

        if ($dryRun) {
            $this->warn('Run without --dry-run to apply these changes');
        }

        return 0;
    }

    private function recomputeSaleItemReturnedQuantities($dryRun = false)
    {
        $this->info('🔢 Recomputing Sale Item Returned Quantities...');

        $saleItems = SaleItem::all();
        $fixed = 0;

        foreach ($saleItems as $item) {
            // Calculate actual returned quantity from processed returns only
            $actualReturned = DB::table('sale_return_items')
                ->join('sale_returns', 'sale_return_items.sale_return_id', '=', 'sale_returns.id')
                ->where('sale_return_items.sale_item_id', $item->id)
                ->where('sale_returns.status', 'processed')
                ->sum('sale_return_items.quantity');

            if ($item->returned_quantity != $actualReturned) {
                $this->line("  📝 Sale Item #{$item->id}: {$item->returned_quantity} → {$actualReturned}");

                if (!$dryRun) {
                    $item->update(['returned_quantity' => $actualReturned]);
                }
                $fixed++;
            }
        }

        $this->info("  ✓ Sale items checked: {$saleItems->count()}, Fixed: {$fixed}");
        return $fixed;
    }

    private function recomputeShiftTotals($dryRun = false)
    {
        $this->info('🏪 Recomputing Shift Return Totals...');

        $shifts = SalesShift::all();
        $fixed = 0;

        foreach ($shifts as $shift) {
            $changes = [];

            // Get actual return counts and amounts for this shift
            $allReturns = SaleReturn::where('sales_shift_id', $shift->id);
            $processedReturns = SaleReturn::where('sales_shift_id', $shift->id)->where('status', 'processed');

            $correctTotalReturnsCount = $allReturns->count();
            $correctProcessedReturnsCount = $processedReturns->count();
            $correctTotalReturnsAmount = $processedReturns->sum('refund_amount');
            $correctProcessedReturnsAmount = $correctTotalReturnsAmount; // Same as total for processed

            // Calculate cash refunds only - FIXED: Use shift_id instead of sales_shift_id
            $cashRefunds = SaleReturn::where('sales_shift_id', $shift->id)
                ->where('status', 'processed')
                ->where('type', 'refund')
                ->whereHas('sale', function ($q) {
                    $q->where('payment_method', 'cash');
                })
                ->sum('refund_amount');

            // Calculate correct cash sales - FIXED: Use shift_id
            $originalCashSales = Sale::where('shift_id', $shift->id)
                ->where('payment_method', 'cash')
                ->where('status', 'completed')
                ->sum('total_amount');
            $correctCashSales = $originalCashSales - $cashRefunds;

            // Calculate correct total sales - FIXED: Use shift_id
            $originalTotalSales = Sale::where('shift_id', $shift->id)
                ->where('status', 'completed')
                ->sum('total_amount');
            $correctTotalSales = $originalTotalSales - $correctTotalReturnsAmount;

            // Check what needs updating
            if ($shift->total_returns_count != $correctTotalReturnsCount) {
                $changes['total_returns_count'] = "{$shift->total_returns_count} → {$correctTotalReturnsCount}";
            }
            if ($shift->processed_returns_count != $correctProcessedReturnsCount) {
                $changes['processed_returns_count'] = "{$shift->processed_returns_count} → {$correctProcessedReturnsCount}";
            }
            if ($shift->total_returns_amount != $correctTotalReturnsAmount) {
                $changes['total_returns_amount'] = "₱{$shift->total_returns_amount} → ₱{$correctTotalReturnsAmount}";
            }
            if ($shift->processed_returns_amount != $correctProcessedReturnsAmount) {
                $changes['processed_returns_amount'] = "₱{$shift->processed_returns_amount} → ₱{$correctProcessedReturnsAmount}";
            }
            if (abs($shift->cash_sales - $correctCashSales) > 0.01) {
                $changes['cash_sales'] = "₱{$shift->cash_sales} → ₱{$correctCashSales}";
            }
            if (abs($shift->total_sales - $correctTotalSales) > 0.01) {
                $changes['total_sales'] = "₱{$shift->total_sales} → ₱{$correctTotalSales}";
            }

            if (!empty($changes)) {
                $this->line("  📝 Shift #{$shift->shift_number}:");
                foreach ($changes as $field => $change) {
                    $this->line("    • {$field}: {$change}");
                }

                if (!$dryRun) {
                    $shift->update([
                        'total_returns_count' => $correctTotalReturnsCount,
                        'processed_returns_count' => $correctProcessedReturnsCount,
                        'total_returns_amount' => $correctTotalReturnsAmount,
                        'processed_returns_amount' => $correctProcessedReturnsAmount,
                        'cash_sales' => $correctCashSales,
                        'total_sales' => $correctTotalSales,
                    ]);
                }
                $fixed++;
            }
        }

        $this->info("  ✓ Shifts checked: {$shifts->count()}, Fixed: {$fixed}");
        return $fixed;
    }
}
