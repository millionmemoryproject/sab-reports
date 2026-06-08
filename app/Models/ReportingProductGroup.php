<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportingProductGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function rules()
    {
        return $this->hasMany(ReportingProductGroupRule::class)
            ->orderBy('priority')
            ->orderBy('id');
    }
}