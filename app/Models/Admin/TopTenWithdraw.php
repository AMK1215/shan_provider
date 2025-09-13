<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopTenWithdraw extends Model
{
    use HasFactory;

    protected $primaryKey = 'id'; // or your actual primary key field

    protected $fillable = [
        'player_id',
        'amount',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'agent_id'); // The admin that owns the banner text
    }
}
