<?php

namespace App\Console\Commands;

use App\Models\WooCommerceOrder;
use App\Models\WooCommerceOrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncWooCommerceOrders extends Command
{
    protected $signature = 'woocommerce:sync-orders 
                            {--start= : Start date, example 2026-01-01}
                            {--end= : End date, example 2026-12-31}
                            {--status= : WooCommerce order status, example completed}';

    protected $description = 'Sync WooCommerce orders and line items into the local database';

    public function handle(): int
    {
        $storeUrl = rtrim(config('services.woocommerce.store_url'), '/');
        $consumerKey = config('services.woocommerce.consumer_key');
        $consumerSecret = config('services.woocommerce.consumer_secret');

        if (! $storeUrl || ! $consumerKey || ! $consumerSecret) {
            $this->error('WooCommerce credentials are missing. Check .env and config/services.php.');
            return self::FAILURE;
        }

        $page = 1;
        $perPage = 100;
        $syncedOrders = 0;
        $syncedItems = 0;

        $params = [
            'per_page' => $perPage,
            'page' => $page,
            'orderby' => 'date',
            'order' => 'asc',
        ];

        if ($this->option('start')) {
            $params['after'] = $this->option('start') . 'T00:00:00';
        }

        if ($this->option('end')) {
            $params['before'] = $this->option('end') . 'T23:59:59';
        }

        if ($this->option('status')) {
            $params['status'] = $this->option('status');
        }

        $this->info('Syncing WooCommerce orders from ' . $storeUrl);

        do {
            $params['page'] = $page;

            $response = Http::withBasicAuth($consumerKey, $consumerSecret)
                ->timeout(60)
                ->get($storeUrl . '/wp-json/wc/v3/orders', $params);

            if (! $response->successful()) {
                $this->error('WooCommerce API error: HTTP ' . $response->status());
                $this->line($response->body());
                return self::FAILURE;
            }

            $orders = $response->json();

            if (empty($orders)) {
                break;
            }

            foreach ($orders as $orderData) {
                $billing = $orderData['billing'] ?? [];
                $shipping = $orderData['shipping'] ?? [];

                $order = WooCommerceOrder::updateOrCreate(
                    ['woo_order_id' => $orderData['id']],
                    [
                        'order_number' => $orderData['number'] ?? null,
                        'status' => $orderData['status'] ?? null,
                        'date_created' => $this->parseDate($orderData['date_created'] ?? null),
                        'date_paid' => $this->parseDate($orderData['date_paid'] ?? null),
                        'currency' => $orderData['currency'] ?? null,

                        'discount_total' => $this->money($orderData['discount_total'] ?? null),
                        'shipping_total' => $this->money($orderData['shipping_total'] ?? null),
                        'tax_total' => $this->money($orderData['total_tax'] ?? null),
                        'total' => $this->money($orderData['total'] ?? null),

                        'payment_method' => $orderData['payment_method'] ?? null,
                        'payment_method_title' => $orderData['payment_method_title'] ?? null,
                        'transaction_id' => $orderData['transaction_id'] ?? null,

                        'customer_id' => isset($orderData['customer_id'])
                            ? (string) $orderData['customer_id']
                            : null,

                        'customer_email' => $billing['email'] ?? null,

                        'billing_first_name' => $billing['first_name'] ?? null,
                        'billing_last_name' => $billing['last_name'] ?? null,
                        'billing_company' => $billing['company'] ?? null,
                        'billing_address_1' => $billing['address_1'] ?? null,
                        'billing_address_2' => $billing['address_2'] ?? null,
                        'billing_city' => $billing['city'] ?? null,
                        'billing_state' => $billing['state'] ?? null,
                        'billing_postcode' => $billing['postcode'] ?? null,
                        'billing_country' => $billing['country'] ?? null,
                        'billing_email' => $billing['email'] ?? null,
                        'billing_phone' => $billing['phone'] ?? null,

                        'shipping_first_name' => $shipping['first_name'] ?? null,
                        'shipping_last_name' => $shipping['last_name'] ?? null,
                        'shipping_company' => $shipping['company'] ?? null,
                        'shipping_address_1' => $shipping['address_1'] ?? null,
                        'shipping_address_2' => $shipping['address_2'] ?? null,
                        'shipping_city' => $shipping['city'] ?? null,
                        'shipping_state' => $shipping['state'] ?? null,
                        'shipping_postcode' => $shipping['postcode'] ?? null,
                        'shipping_country' => $shipping['country'] ?? null,

                        'meta_data' => $orderData['meta_data'] ?? [],
                        'raw_order' => $orderData,
                    ]
                );

                $existingLineItemIds = [];

                foreach (($orderData['line_items'] ?? []) as $itemData) {
                    $lineItemId = $itemData['id'] ?? null;

                    $item = WooCommerceOrderItem::updateOrCreate(
                        [
                            'woo_order_id' => $orderData['id'],
                            'woo_line_item_id' => $lineItemId,
                        ],
                        [
                            'woo_commerce_order_id' => $order->id,
                            'product_id' => $itemData['product_id'] ?? null,
                            'variation_id' => $itemData['variation_id'] ?? null,
                            'name' => $itemData['name'] ?? null,
                            'sku' => $itemData['sku'] ?? null,
                            'quantity' => $itemData['quantity'] ?? 0,
                            'subtotal' => $this->money($itemData['subtotal'] ?? null),
                            'subtotal_tax' => $this->money($itemData['subtotal_tax'] ?? null),
                            'total' => $this->money($itemData['total'] ?? null),
                            'total_tax' => $this->money($itemData['total_tax'] ?? null),
                            'taxes' => $itemData['taxes'] ?? [],
                            'meta_data' => $itemData['meta_data'] ?? [],
                            'raw_item' => $itemData,
                        ]
                    );

                    $existingLineItemIds[] = $lineItemId;
                    $syncedItems++;
                }

                if (! empty($existingLineItemIds)) {
                    WooCommerceOrderItem::where('woo_commerce_order_id', $order->id)
                        ->whereNotIn('woo_line_item_id', $existingLineItemIds)
                        ->delete();
                }

                $syncedOrders++;
            }

            $this->info("Synced page {$page} — total orders: {$syncedOrders}");

            $page++;
        } while (count($orders) === $perPage);

        $this->info("Done. Synced {$syncedOrders} orders and {$syncedItems} line items.");

        return self::SUCCESS;
    }

    protected function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime($value));
    }

    protected function money($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}