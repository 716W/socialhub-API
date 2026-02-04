<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = "user_profiles";
    protected $fillable = [
        'user_id',
        'username',
        'bio',
        'avatar',
        'website',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
