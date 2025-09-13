<?php

namespace App\Models\TwoDigit;

use App\Models\TwoDigit\TwoBet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwoBetSlip extends Model
{
    use HasFactory;

    protected $table = 'two_bet_slips';

    protected $fillable = [
        'slip_no',
        'user_id',
        'player_name',
        'agent_id',
        'total_bet_amount',
        'session',
        'status',
        'game_date',
        'game_time',
        'before_balance',
        'after_balance',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // slip no
    public function twoBets()
    {
        return $this->hasMany(TwoBet::class, 'slip_id', 'id');
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
