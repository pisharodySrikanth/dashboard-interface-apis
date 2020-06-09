<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    public $guarded = [];

    public function impressions() {
        return $this->hasMany('App\Impression');
    }
}
