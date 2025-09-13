<?php

namespace App\Models\TwoDigit; // Adjust namespace as per your application's structure

use App\Models\TwoDigit\Bettle; // Assuming your User model is in App\Models
use App\Models\TwoDigit\ChooseDigit; // Assuming Bettle model is in App\Models\TwoDigit
use App\Models\TwoDigit\HeadClose; // Assuming ChooseDigit model is in App\Models\TwoDigit
use App\Models\User; // Assuming HeadClose model is in App\Models\TwoDigit
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwoBet extends Model
{
    use HasFactory;

    protected $table = 'two_bets'; // Explicitly define table name

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'member_name', // If you decide to keep this despite redundancy
        'bettle_id',
        'choose_digit_id',
        'head_close_id',
        'agent_id',
        'bet_number',
        'bet_amount',
        'total_bet_amount', // Corresponds to the 'total_bet_amount' in your migration
        'session', // If you decide to keep this despite redundancy
        'win_lose', // If you decide to keep this despite redundancy
        'potential_payout',
        'bet_status',
        'bet_result',
        'game_date', // Adjusted based on previous suggestions
        'game_time', // Adjusted based on previous suggestions
        'prize_sent',
        'slip_id',
    ];

    // Cast properties for automatic type conversion
    protected $casts = [
        'bet_amount' => 'decimal:2',
        'total_bet_amount' => 'decimal:2',
        'potential_payout' => 'decimal:2',
        'bet_status' => 'boolean',
        'win_lose' => 'boolean', // If you keep this column
        'game_date' => 'date',
        'game_time' => 'datetime', // Cast time fields to datetime for Carbon instances
        'before_balance' => 'decimal:2',
        'after_balance' => 'decimal:2',
    ];

    /**
     * Get the user that placed the bet.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the battle session the bet belongs to.
     */
    public function battle()
    {
        return $this->belongsTo(Bettle::class, 'bettle_id');
    }

    /**
     * Get the chosen digit for the bet.
     */
    public function chooseDigit()
    {
        return $this->belongsTo(ChooseDigit::class, 'choose_digit_id');
    }

    /**
     * Get the head/close digit associated with the bet.
     */
    public function headClose()
    {
        return $this->belongsTo(HeadClose::class, 'head_close_id');
    }

    /**
     * Get the agent associated with the bet.
     * Assumes 'agent_id' refers to a user who is an agent.
     * If you have a separate 'Agent' model, change 'User::class' to 'Agent::class'.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
        // If you have a dedicated Agent model (e.g., App\Models\Agent)
        // return $this->belongsTo(Agent::class, 'agent_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->game_date = Carbon::now('Asia/Yangon')->format('Y-m-d');
            $model->game_time = Carbon::now('Asia/Yangon')->format('H:i:s');
            $model->created_at = Carbon::now('Asia/Yangon');
            $model->updated_at = Carbon::now('Asia/Yangon');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Yangon');
        });
    }
}
