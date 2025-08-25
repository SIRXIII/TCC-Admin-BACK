<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'profile_photo' => $this->profile_photo,
            'rider_id' => $this->rider_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'image' => $this->image,
            'online' => $this->online ? 'online' : 'offline',
            'delivered_orders' => $this->delivered_orders,
            'average_rating' => $this->average_rating,
            'current_assigned_orders' => $this->current_assigned_orders,
        ];
    }
}
