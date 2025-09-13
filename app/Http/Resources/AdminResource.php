<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
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
            'user_name' => $this->user_name,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'profile' => $this->profile,
            'role' => $this->roles->pluck('name'),
            'balance' => $this->balanceFloat,
            'max_score' => $this->max_score,
            'status' => $this->status,
            'is_changed_password' => $this->is_changed_password,
            'agent_id' => $this->agent_id,
            'payment_type_id' => $this->payment_type_id,
            'agent_logo' => $this->agent_logo,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'line_id' => $this->line_id,
            'commission' => $this->commission,
            'referral_code' => $this->referral_code,
            'site_name' => $this->site_name,
            'site_link' => $this->site_link,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
