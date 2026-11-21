<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentOtp extends Model
{
    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used'    => 'boolean',
        'used_at'    => 'datetime',
    ];
}
