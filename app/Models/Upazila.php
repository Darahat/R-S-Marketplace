<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upazila extends Model
{
    public function unions() {
        return $this->hasMany(Union::class);
    }
}
