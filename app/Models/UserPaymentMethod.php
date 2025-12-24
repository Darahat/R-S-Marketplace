<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stripe_payment_method_id',
        'card_brand',
        'card_last4',
        'card_exp_month',
        'card_exp_year',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'card_exp_month' => 'integer',
        'card_exp_year' => 'integer',
    ];

    /**
     * Get the user that owns this payment method
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted card display (e.g., "Visa ****1234")
     */
    public function getCardDisplayAttribute()
    {
        return ucfirst($this->card_brand) . ' ••••' . $this->card_last4;
    }

    /**
     * Check if card is expired
     */
    public function isExpired()
    {
        $now = now();
        return $this->card_exp_year < $now->year ||
               ($this->card_exp_year == $now->year && $this->card_exp_month < $now->month);
    }
}
