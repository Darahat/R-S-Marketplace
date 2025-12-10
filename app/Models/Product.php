<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'purchase_price',
        'discount_price',
        'sold_count',
        'featured',
        'image',
        'category_id',
        'brand_id',
        'stock',
        'is_best_selling',
        'is_latest',
        'is_flash_sale',
        'is_todays_deal',
        'rating',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'featured' => 'boolean',
        'is_best_selling' => 'boolean',
        'is_latest' => 'boolean',
        'is_flash_sale' => 'boolean',
        'is_todays_deal' => 'boolean',
        'rating' => 'decimal:1',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->discount_price > 0) {
            return round((($this->price - $this->discount_price) / $this->price) * 100);
        }
        return 0;
    }

    public function getFinalPriceAttribute()
    {
        return $this->discount_price > 0 ? $this->discount_price : $this->price;
    }

    public function getImageUrlAttribute()
    {
        return $this->image ?? 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500';
    }
}
