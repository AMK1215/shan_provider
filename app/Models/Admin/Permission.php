<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static whereIn(string $string, string[] $array)
 */
class Permission extends Model
{
    use HasFactory;

    public $table = 'permissions';

    protected $fillable = [
        'title',
        'group',
        'created_at',
        'updated_at',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);

    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
