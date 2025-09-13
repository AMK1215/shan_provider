<?php

namespace App\Models\TwoDigit;

use App\Models\TwoDigit\TwoBet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChooseDigit extends Model
{
    use HasFactory;

    protected $fillable = ['choose_close_digit', 'status'];

    public function twoBets()
    {
        return $this->hasMany(TwoBet::class, 'choose_digit_id');
    }
}
