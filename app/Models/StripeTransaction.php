<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeTransaction extends Model
{
    protected $fillable = [
        'stripe_id',
        'transaction_date',
        'type',
        'reporting_category',
        'currency',
        'gross',
        'fee',
        'net',
        'status',
        'description',
        'source',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'gross' => 'decimal:2',
        'fee' => 'decimal:2',
        'net' => 'decimal:2',
    ];
}