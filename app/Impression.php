<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Impression extends Model
{
    public $guarded = [];
    
    public function resource() {
        return $this->belongsTo('App\Resource');
    }
}
