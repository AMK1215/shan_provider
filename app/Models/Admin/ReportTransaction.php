<?php

namespace App\Models\Admin;

use App\Models\GameType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ReportTransaction extends Model
{
    use HasFactory;

    protected $table = 'report_transactions';

    protected $primaryKey = 'id';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'agent_id',
        'agent_code',
        'member_account',
        'transaction_amount',
        'bet_amount',
        'valid_amount',
        'status',
        'banker',
        'before_balance',
        'after_balance',
        'wager_code',
        'settled_status',
    ];

    protected $casts = [

        'transaction_amount' => 'decimal:2',
        'bet_amount' => 'decimal:2',
        'valid_amount' => 'decimal:2',
        'before_balance' => 'decimal:2',
        'after_balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTransactionAmountAttribute($value)
    {
        return number_format($value, 2);
    }

    public function getBetAmountAttribute($value)
    {
        return number_format($value, 2);
    }

    public function getValidAmountAttribute($value)
    {
        return number_format($value, 2);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Yangon');
            $model->updated_at = Carbon::now('Asia/Yangon');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Yangon');
        });
    }
}
