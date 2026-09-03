<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'thumbnail',
        'type',
        'sort_order',
        'status',
        'is_nickname_check_enabled',
        'nickname_check_provider',
        'digiflazz_inquiry_sku',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_nickname_check_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
}
