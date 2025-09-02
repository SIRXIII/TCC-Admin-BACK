<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'traveler_name' => $this->traveler->name,
            'partner_name' => $this->partner?->name,
            'partner_photo' => $this->partner->profile_photo,
            'items_count'   => $this->items->count(),
            'total_price'   => $this->total_price,
            'status'        => $this->status,
            'created_at'    => $this->created_at->format('F d, Y'),
        ];
    }
}
