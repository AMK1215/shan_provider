<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->products->map(function ($item) {

            return [
                'id' => $item->id,
                'provider' => $item->provider,
                'currency' => $item->currency,
                'status' => $item->status,
                'provider_id' => $item->provider_id,
                'product_id' => $item->id,
                'product_code' => $item->product_code,
                'product_name' => $item->product_name,
                'game_type' => $item->game_type,
                'product_title' => $item->product_title,
                'short_name' => $item->short_name,
                'order' => $item->order,
                'game_list_status' => $item->game_list_status,
                'img_url' => $item->img_url,  // Make sure this accessor exists in your model
            ];
        })->toArray();
    }
}
