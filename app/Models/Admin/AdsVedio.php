<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdsVedio extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_ads',
        'admin_id',
    ];

    protected $appends = ['video_url'];

    public function admin()
    {
        return $this->belongsTo(User::class, 'agent_id'); // The admin that owns the banner
    }

    public function getVideoUrlAttribute()
    {
        return asset('assets/img/video_ads/'.$this->video_ads);
    }
}
