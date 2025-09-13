<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerText extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'agent_id'); // The admin that owns the banner text
    }
}
