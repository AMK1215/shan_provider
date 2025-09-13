<?php

namespace App\Models;

use App\Models\Admin\Bank;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function Ramsey\Uuid\v1;

class DepositRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'agent_id', 'agent_payment_type_id', 'amount', 'status', 'note', 'refrence_no', 'image', 'sub_agent_id', 'sub_agent_name'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'agent_payment_type_id');
    }
}
