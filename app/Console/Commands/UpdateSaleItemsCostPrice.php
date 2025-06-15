<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SaleItem;
use App\Models\Product;

class UpdateSaleItemsCostPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sale-items:update-cost-price
                            {--dry-run : Run without making actual changes}
                            {--batch-size=1000 : Number of records to process at once}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing sale items with cost prices from products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $batchSize = $this->option('batch-size');

        $this->info('Starting cost price update for sale items...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get count of sale items with missing cost price
        $totalItems = SaleItem::whereNull('cost_price')
            ->orWhere('cost_price', 0)
            ->count();

        if ($totalItems === 0) {
            $this->info('No sale items need cost price updates.');
            return 0;
        }

        $this->info("Found {$totalItems} sale items that need cost price updates.");

        $progressBar = $this->output->createProgressBar($totalItems);
        $progressBar->start();

        $updated = 0;
        $errors = 0;

        // Process in batches
        SaleItem::whereNull('cost_price')
            ->orWhere('cost_price', 0)
            ->with('product')
            ->chunk($batchSize, function ($saleItems) use (&$updated, &$errors, $dryRun, $progressBar) {
                foreach ($saleItems as $saleItem) {
                    try {
                        if ($saleItem->product && $saleItem->product->cost_price > 0) {
                            if (!$dryRun) {
                                $saleItem->update([
                                    'cost_price' => $saleItem->product->cost_price
                                ]);
                            }
                            $updated++;

                            $this->line("\nUpdated SaleItem ID {$saleItem->id}: Cost price set to ₱{$saleItem->product->cost_price}");
                        } else {
                            $this->warn("\nSaleItem ID {$saleItem->id}: Product has no cost price or product not found");
                        }
                    } catch (\Exception $e) {
                        $errors++;
                        $this->error("\nError updating SaleItem ID {$saleItem->id}: " . $e->getMessage());
                    }

                    $progressBar->advance();
                }
            });

        $progressBar->finish();

        $this->newLine(2);
        $this->info("Update completed!");
        $this->info("Items updated: {$updated}");
        $this->info("Errors: {$errors}");

        if ($dryRun) {
            $this->warn('DRY RUN - No actual changes were made. Run without --dry-run to apply changes.');
        }

        return 0;
    }
}
