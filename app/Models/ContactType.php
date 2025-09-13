<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
    ];

    protected $appends = ['img_url'];

    public function getImgUrlAttribute()
    {
        return asset('assets/img/contacts/'.$this->image);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
