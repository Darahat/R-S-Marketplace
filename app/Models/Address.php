<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_type',
        'full_name',
        'phone',
        'email',
        'district_id',
        'upazila_id',
        'union_id',
        'street_address',
        'postal_code',
        'country',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the address.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the district.
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the upazila.
     */
    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }

    /**
     * Get the union.
     */
    public function union()
    {
        return $this->belongsTo(Union::class);
    }
}
