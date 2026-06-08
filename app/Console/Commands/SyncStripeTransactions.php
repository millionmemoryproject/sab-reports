<?php

namespace App\Console\Commands;

use App\Models\StripeTransaction;
use Illuminate\Console\Command;
use Stripe\StripeClient;

class SyncStripeTransactions extends Command
{
    protected $signature = 'stripe:sync-transactions 
                            {--start= : Start date, example 2026-01-01}
                            {--end= : End date, example 2026-12-31}';

    protected $description = 'Sync Stripe balance transactions into the local database';

    public function handle(): int
    {
        $startDate = $this->option('start') ?: now()->subDays(30)->toDateString();
        $endDate = $this->option('end') ?: now()->toDateString();

        $start = strtotime($startDate . ' 00:00:00');
        $end = strtotime($endDate . ' 23:59:59');

        $stripe = new StripeClient(config('services.stripe.secret'));

        $this->info("Syncing Stripe transactions from {$startDate} to {$endDate}...");

        $count = 0;

        foreach ($stripe->balanceTransactions->all([
            'limit' => 100,
            'created' => [
                'gte' => $start,
                'lte' => $end,
            ],
        ])->autoPagingIterator() as $transaction) {
            StripeTransaction::updateOrCreate(
                ['stripe_id' => $transaction->id],
                [
                    'transaction_date' => date('Y-m-d H:i:s', $transaction->created),
                    'type' => $transaction->type,
                    'reporting_category' => $transaction->reporting_category,
                    'currency' => strtoupper($transaction->currency),
                    'gross' => $transaction->amount / 100,
                    'fee' => $transaction->fee / 100,
                    'net' => $transaction->net / 100,
                    'status' => $transaction->status,
                    'description' => $transaction->description,
                    'source' => $transaction->source,
                ]
            );

            $count++;
        }

        $this->info("Synced {$count} Stripe transactions.");

        return self::SUCCESS;
    }
}