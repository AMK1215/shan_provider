<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Product extends Model
{
    use HasFactory;

    // protected $fillable = ['code', 'name', 'short_name', 'order', 'status', 'game_list_status'];
    protected $fillable = [
        'provider',
        'currency',
        'status',
        'provider_id',
        'provider_product_id',
        'product_code',
        'product_name',
        'game_type',
        'product_title',
        'short_name',
        'order',
        'game_list_status',
    ];

    protected $appends = ['imgUrl']; // Changed from 'image' to 'imgUrl'
    // protected $appends = ['image'];

    public function gameTypes()
    {
        return $this->belongsToMany(GameType::class)->withPivot('image');
    }

    public function getImgUrlAttribute()
    {
        if (isset($this->pivot) && ! empty($this->pivot->image)) {
            return asset('assets/img/game_logo/'.$this->pivot->image);
        }

        // Optional: Return a default image if pivot image is missing
        return asset('assets/img/default.png'); // or null
    }

    /**
     * Toggle the status between 1 and 0.
     *
     * @return bool
     */
    public function toggleStatus()
    {
        $this->status = $this->status == 'ACTIVATED' ? 'UNACTIVATED' : 'ACTIVATED';

        return $this->save();
    }

    public function digitBets()
    {
        return $this->hasMany(DigitBet::class, 'product_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Yangon');
            $model->updated_at = Carbon::now('Asia/Yangon');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Yangon');
        });
    }
}
