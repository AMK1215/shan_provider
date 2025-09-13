<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
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
            'name' => $this->account_name,
            'no' => $this->account_number,
            'bank_id' => $this->payment_type_id,
            'bank_name' => $this->paymentType->name,
            'img' => $this->paymentType->image_url,
        ];
    }
}
