<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Traveler extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'profile_photo',
        'country',
        'email',
        'phone',
        'spent_amount',
        'address',
        'username',
        'password',
        'status',
        'last_active',
    ];

    protected $hidden = ['password'];

     protected $casts = [
        'last_active' => 'datetime',
    ];

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function shippingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'shipping');
    }

    public function billingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'billing');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
