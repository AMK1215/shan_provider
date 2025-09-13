<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameListResource extends JsonResource
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
            'game_code' => $this->game_code,
            'game_name' => $this->game_name,
            'game_type' => $this->game_type,
            'image_url' => $this->image_url,
            'provider_product_id' => $this->provider_product_id,
            'game_type_id' => $this->game_type_id,
            'product_id' => $this->product_id,
            'product_code' => $this->product_code,
            'support_currency' => $this->support_currency,
            'status' => $this->status,
            'is_active' => (bool) $this->is_active,
            'provider' => $this->provider,
            'order' => $this->order,
        ];
        // return [
        //     'id' => $this->id,
        //     'name' => $this->name,
        //     'code' => $this->code,
        //     'img' => $this->image_url,
        //     'type_id' => $this->game_type_id,
        //     'provider_id' => $this->product_id,
        //     'provider_code' => $this->product->code,
        // ];
    }
}
