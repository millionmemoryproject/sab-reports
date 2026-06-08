<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WooCommerceOrder extends Model
{
    protected $fillable = [
        'woo_order_id',
        'order_number',
        'status',
        'date_created',
        'date_paid',
        'currency',
        'discount_total',
        'shipping_total',
        'tax_total',
        'total',
        'payment_method',
        'payment_method_title',
        'transaction_id',
        'customer_id',
        'customer_email',
        'billing_first_name',
        'billing_last_name',
        'billing_company',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_state',
        'billing_postcode',
        'billing_country',
        'billing_email',
        'billing_phone',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_company',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_country',
        'matched_stripe_transaction_id',
        'match_status',
        'match_method',
        'match_confidence',
        'match_notes',
        'meta_data',
        'raw_order',
    ];

    protected $casts = [
        'date_created' => 'datetime',
        'date_paid' => 'datetime',
        'discount_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'meta_data' => 'array',
        'raw_order' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(WooCommerceOrderItem::class);
    }

    public function stripeTransaction()
    {
        return $this->belongsTo(StripeTransaction::class, 'matched_stripe_transaction_id');
    }
}