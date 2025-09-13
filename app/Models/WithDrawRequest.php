<?php

namespace App\Models;

use App\Models\Admin\Bank;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithDrawRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'agent_id', 'amount', 'status', 'note', 'payment_type_id', 'account_name', 'account_number', 'sub_agent_id', 'sub_agent_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }
}
