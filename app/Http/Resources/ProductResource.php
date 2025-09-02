<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'partner_id'        => $this->partner_id,
            'name'              => $this->name,
            'brand'             => $this->brand,
            'color'             => $this->color,
            'material'          => $this->material,
            'care_method'       => $this->care_method,
            'weight'            => $this->weight,
            'sku'               => $this->sku,
            'base_price'        => $this->base_price,
            'deposit'           => $this->deposit,
            'late_fee'          => $this->late_fee,
            'replacement_value' => $this->replacement_value,
            'buy_price'         => $this->buy_price,
            'extensions'        => $this->extensions,
            'prep_buffer'       => $this->prep_buffer,
            'min_rental'        => $this->min_rental,
            'max_rental'        => $this->max_rental,
            'blackout_date'     => $this->blackout_date,
            'location'          => $this->location,
            'fit_category'      => $this->fit_category,
            'length_unit'       => $this->length_unit,
            'length'            => $this->length,
            'chest'             => $this->chest,
            'sleeve'            => $this->sleeve,
            'condition_grade'   => $this->condition_grade,
            'status'            => $this->status,
            'note'              => $this->note,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
