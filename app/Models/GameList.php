<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameList extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_code',
        'game_name',
        'game_type',
        'image_url',
        'provider_product_id',
        'game_type_id',
        'product_id',
        'product_code',
        'support_currency',
        'status',
        'is_active',
        'provider',
        'order',
        'hot_status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function gameType()
    {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }

    public function getImgUrlAttribute()
    {
        return asset('/game_logo/'.$this->image);
    }

    public function scopeHotGame($query)
    {
        return $this->where('status', 1)->where('hot_status', 1);
    }
}
