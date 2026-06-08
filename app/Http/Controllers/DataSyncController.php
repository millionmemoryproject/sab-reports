<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class DataSyncController extends Controller
{
    public function resync()
    {
        $start = '2010-01-01';
        $end = now()->toDateString();

        Artisan::call('stripe:sync-transactions', [
            '--start' => $start,
            '--end' => $end,
        ]);

        Artisan::call('woocommerce:sync-orders', [
            '--start' => $start,
            '--end' => $end,
        ]);

        Artisan::call('reports:reconcile-stripe-woocommerce');

        return redirect()
            ->back()
            ->with('status', 'Stripe and WooCommerce data re-synced successfully.');
    }
}