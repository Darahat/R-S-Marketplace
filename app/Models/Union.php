<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Union extends Model
{
     public function upazila() {
        return $this->belongsTo(Upazila::class);
    }
    public function district() {
        return $this->belongsTo(District::class);
    }
}
