<?php

namespace App\Models\TwoDigit;

use App\Models\TwoDigit\TwoBet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeadClose extends Model
{
    use HasFactory;

    protected $fillable = ['head_close_digit', 'status'];

    public function twoBets()
    {
        return $this->hasMany(TwoBet::class, 'head_close_id');
    }
}
