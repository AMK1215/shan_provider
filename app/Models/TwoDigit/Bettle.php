<?php

namespace App\Models\TwoDigit;

use App\Models\TwoDigit\TwoBet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bettle extends Model
{
    use HasFactory;

    protected $table = 'battles';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = ['battle_name', 'start_time', 'end_time', 'status', 'open_date'];

    // Cast 'status' to boolean for easier handling (true/false)
    // Cast time fields to Carbon instances for easier formatting in Blade
    protected $casts = [
        'status' => 'boolean',
        'start_time' => 'datetime', // Cast to Carbon instance for time objects
        'end_time' => 'datetime',   // Cast to Carbon instance for time objects
        'open_date' => 'date',
    ];

    // Accessor to get formatted start time
    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time ? $this->start_time->format('h:i A') : null;
    }

    // Accessor to get formatted end time
    public function getFormattedEndTimeAttribute()
    {
        return $this->end_time ? $this->end_time->format('h:i A') : null;
    }

    public function twoBets()
    {
        return $this->hasMany(TwoBet::class, 'bettle_id');
    }
}
