<?php

namespace App\Console\Commands;

use App\Models\StripeTransaction;
use App\Models\WooCommerceOrder;
use Illuminate\Console\Command;

class ReconcileStripeWooCommerce extends Command
{
    protected $signature = 'reports:reconcile-stripe-woocommerce';

    protected $description = 'Match WooCommerce orders to Stripe transactions';

    public function handle(): int
    {
        $matched = 0;
        $unmatched = 0;

        WooCommerceOrder::query()
            ->orderBy('id')
            ->chunk(100, function ($orders) use (&$matched, &$unmatched) {
                foreach ($orders as $order) {
                    if (! $order->transaction_id) {
                        $order->update([
                            'match_status' => 'unmatched',
                            'match_method' => null,
                            'match_confidence' => null,
                            'match_notes' => 'WooCommerce order has no transaction_id.',
                        ]);

                        $unmatched++;
                        continue;
                    }

                    $stripeTransaction = StripeTransaction::query()
                        ->where('source', $order->transaction_id)
                        ->first();

                    if ($stripeTransaction) {
                        $order->update([
                            'matched_stripe_transaction_id' => $stripeTransaction->id,
                            'match_status' => 'matched',
                            'match_method' => 'woocommerce_transaction_id_to_stripe_source',
                            'match_confidence' => 'high',
                            'match_notes' => null,
                        ]);

                        $matched++;
                        continue;
                    }

                    $order->update([
                        'matched_stripe_transaction_id' => null,
                        'match_status' => 'unmatched',
                        'match_method' => 'woocommerce_transaction_id_to_stripe_source',
                        'match_confidence' => 'none',
                        'match_notes' => 'No Stripe transaction found where source equals WooCommerce transaction_id: ' . $order->transaction_id,
                    ]);

                    $unmatched++;
                }
            });

        $this->info("Matched: {$matched}");
        $this->info("Unmatched: {$unmatched}");

        return self::SUCCESS;
    }
}