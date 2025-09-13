<?php

namespace App\Models\TwoDigit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlipNumberCounter extends Model
{
    use HasFactory;

    protected $table = 'slip_number_counters';

    protected $fillable = ['current_number'];
}
