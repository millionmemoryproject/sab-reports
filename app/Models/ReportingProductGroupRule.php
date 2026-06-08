<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportingProductGroupRule extends Model
{
    protected $fillable = [
        'reporting_product_group_id',
        'match_field',
        'match_operator',
        'match_value',
        'level_name',
        'priority',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(ReportingProductGroup::class, 'reporting_product_group_id');
    }
}