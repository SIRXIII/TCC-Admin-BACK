<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'profile_photo',
        'first_name',
        'last_name',
        'email',
        'phone',
        'category',
        'location',
        'documents',
        'username',
        'password',
        'status',
        'average_rating',
    ];

    protected $hidden = ['password'];

    public function products()
    {
        return $this->hasMany(Product::class, 'partner_id');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getAverageRatingAttribute()
    {
        return number_format($this->ratings()->avg('rating') ?? 0, 1);
    }

    public function getFullNameAttribute(){
        return $this->first_name  . ' ' .  $this->last_name;
    }
}
