<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeTransactionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
