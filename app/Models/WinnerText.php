<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinnerText extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'text', 'owner_id'];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
