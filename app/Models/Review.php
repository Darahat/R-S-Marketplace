<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'rating',
        'comment',
        'is_verified'
    ];
    protected $casts = [
        'rating' => 'decimal:1',
        'is_verified' => 'boolean',
    ];

    public function product(){
        $this->belongsTo(Product::class);
    }
    public function user(){
        $this->belongsTo(User::class);
    }

}
