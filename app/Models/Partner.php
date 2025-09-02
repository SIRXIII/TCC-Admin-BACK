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
        'name',
        'business_name',
        'email',
        'phone',
        'category',
        'location',
        'address',
        // 'documents',
        'username',
        'password',
        'status',
        'average_rating',
        'store_available_days',
        'store_available_time',
        'tax_id',
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

    public function documents()
    {
        return $this->hasMany(PartnerDocument::class);
    }

    public function licenseDocuments()
    {
        return $this->documents()->where('type', 'license');
    }

    public function ownerIdCardDocuments()
    {
        return $this->documents()->where('type', 'owner_id_card');
    }
}
