<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'category' => $this->category,
            'location' => $this->location,
            'documents' => $this->documents,
            'username' => $this->username,
            'status' => $this->status,
            'total_sales' => $this->total_sales ?? 0,
            'delivered_orders_count' => $this->delivered_orders_count  ?? 0,
            'rating' => $this->average_rating,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
