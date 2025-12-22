<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'transaction_id',
        'stripe_payment_intent_id',
        'stripe_refund_id',
        'payment_method',
        'payment_status',
        'amount',
        'fee',
        'notes',
        'response_data',
        'paid_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order associated with this payment
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who made this payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get total amount (amount + fee)
     */
    public function getTotalAmountAttribute()
    {
        return $this->amount + $this->fee;
    }
}

