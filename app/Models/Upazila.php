<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upazila extends Model
{
    public function union() {
        return $this->hasMany(Union::class);
    }
    public function district() {
        return $this->belongsTo(District::class);
    }
}
