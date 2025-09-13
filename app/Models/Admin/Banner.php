<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'admin_id',
    ];

    protected $appends = ['img_url'];

    public function admin()
    {
        return $this->belongsTo(User::class, 'agent_id'); // The admin that owns the banner
    }

    public function scopeGetAll($query, $id)
    {
        return $query->where('admin_id', $id)->latest();
    }

    public function getImgUrlAttribute()
    {
        return asset('assets/img/banners/'.$this->image);
    }
}
