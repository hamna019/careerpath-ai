<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminId extends Model
{
    protected $fillable = [
        'admin_id',
        'email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'admin_id', 'admin_id');
    }
}
