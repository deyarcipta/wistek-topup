<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'sub_category_id',
        'name',
        'sku',
        'price_cost',
        'price_sell',
        'status',
        'digiflazz_status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'digiflazz_status' => 'boolean',
        'price_cost' => 'decimal:2',
        'price_sell' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
