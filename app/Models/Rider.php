<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
   use HasFactory;

    protected $fillable = [
        'rider_id',
        'name',
        'phone',
        'email',
        'image',
        'online',
        'delivered_orders',
        'average_rating',
        'profile_photo',
    ];


    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
}
