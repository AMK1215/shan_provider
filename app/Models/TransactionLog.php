<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'batch_request',
        'response_data',
        'status',
    ];

    protected $casts = [
        'batch_request' => 'array',
        'response_data' => 'array',
    ];
}
