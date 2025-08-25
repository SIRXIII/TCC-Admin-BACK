<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function images() {
        return $this->hasMany(ProductImage::class);
    }

    public function videos() {
        return $this->hasMany(ProductVideo::class);
    }

     public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

     public function orders()
    {
        return $this->hasMany(Order::class, 'product_id');

    }

}
