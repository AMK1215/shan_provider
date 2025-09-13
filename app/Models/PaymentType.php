<?php

namespace App\Models;

use App\Models\Admin\Bank;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return asset('assets/img/paymentType/'.$this->image);
    }

    public function banks()
    {
        return $this->hasMany(Bank::class);
    }
}
