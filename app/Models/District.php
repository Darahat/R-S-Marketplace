<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    public function upazilas()
    {
        return $this->hasMany(Upazila::class);
    }
}
