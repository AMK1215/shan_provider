<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameType extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'name_mm', 'img', 'status', 'order'];

    protected $appends = ['image', 'img_url'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'game_type_product')->withPivot('image');
    }

    public function getImageAttribute() // getImageAttribute
    {
        return $this->products->pluck('pivot.image');
    }

    // getImgUrlAttribute
    public function getImgUrlAttribute()
    {
        return asset('assets/img/game_type/'.$this->img);
    }

    public function scopeActive($query)
    {
        return $this->where('status', 1);
    }

    /**
     * Toggle the status between 1 and 0.
     *
     * @return bool
     */
    public function GameTypetoggleStatus()
    {
        $this->status = $this->status == 1 ? 0 : 1;

        return $this->save();
    }

    public function digitBets()
    {
        return $this->hasMany(DigitBet::class, 'game_type_id');
    }
}
