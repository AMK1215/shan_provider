<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerAds extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'admin_id',
    ];

    protected $appends = ['img_url'];

    protected $table = 'banner_ads';

    public function admin()
    {
        return $this->belongsTo(User::class, 'agent_id'); // The admin that owns the banner
    }

    public function getImgUrlAttribute()
    {
        return asset('assets/img/banners_ads/'.$this->image);
    }
}
