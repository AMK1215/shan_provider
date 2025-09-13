<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = [
            'id' => $this->id,
            'name' => $this->name,
            'user_name' => $this->user_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'balance' => $this->balanceFloat,
            'main_balance' => $this->main_balance,
            'status' => $this->status,
        ];

        return [
            'user' => $user,
            'token' => $this->createToken($this->user_name)->plainTextToken,
        ];
    }
}
