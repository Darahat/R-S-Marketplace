<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'address_id',
        'order_status',
        'payment_method',
        'payment_status',
        'subtotal',
        'shipping_cost',
        'tax',
        'discount',
        'total_amount',
        'notes',
        'shipped_at',
        'delivered_at',
        'stripe_session_id',
        'stripe_payment_intent_id',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the address.
     */
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get the order items.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get payments associated with this order
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
    /*
     generate subtotal order
    */
     public function getCalcSubtotalAttribute(): float
     {
         return (float) ($this->subtotal ?: $this->items->sum('total'));
     }

     public function getCalcShippingAttribute(): float
     {
         return (float) $this->shipping_cost;
     }

     public function getCalcTaxAttribute(): float
     {
         return (float) $this->tax;
     }

     public function getCalcDiscountAttribute(): float
     {
         return (float) $this->discount;
     }

     public function getCalcTotalAttribute(): float
     {
         if ($this->total_amount) {
             return (float) $this->total_amount;
         }
         return $this->calc_subtotal + $this->calc_shipping + $this->calc_tax - $this->calc_discount;
     }
}
