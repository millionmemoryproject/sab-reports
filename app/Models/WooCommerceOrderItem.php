<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WooCommerceOrderItem extends Model
{
    protected $fillable = [
        'woo_commerce_order_id',
        'woo_order_id',
        'woo_line_item_id',
        'product_id',
        'variation_id',
        'name',
        'sku',
        'quantity',
        'subtotal',
        'subtotal_tax',
        'total',
        'total_tax',
        'taxes',
        'meta_data',
        'raw_item',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
        'subtotal_tax' => 'decimal:2',
        'total' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'taxes' => 'array',
        'meta_data' => 'array',
        'raw_item' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(WooCommerceOrder::class, 'woo_commerce_order_id');
    }
    
}