<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferLog extends Model
{
    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'sub_agent_id',
        'sub_agent_name',
        'amount',
        'type',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'decimal:2',
    ];

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function subAgent()
    {
        return $this->belongsTo(User::class, 'sub_agent_id');
    }
}
