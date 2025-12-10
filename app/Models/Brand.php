<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get categories associated with this brand
     */
    public function categories()
    {
        if (!$this->category_id) {
            return collect([]);
        }

        $categoryIds = explode(',', $this->category_id);
        return Category::whereIn('id', $categoryIds)->get();
    }

    /**
     * Get products associated with this brand
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
