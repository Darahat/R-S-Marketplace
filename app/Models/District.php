<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    public function upazila()
    {
        return $this->hasMany(Upazila::class);
    }
    public function union(){
        return $this->hasMany(Union::class);
    }
}
