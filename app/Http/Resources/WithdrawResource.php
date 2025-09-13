<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawResource extends JsonResource
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
            'name' => $this->user->name,
            'payment_type' => $this->paymentType->name,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'amount' => $this->amount,
            'status' => $this->status === 0 ? 'Pending' : ($this->status === 1 ? 'Success' : 'Reject'),
            'datetime' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
