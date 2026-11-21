<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyActiveTime extends Model
{
    protected $fillable = ['user_id', 'activity_date', 'seconds'];

    protected $casts = [
        'activity_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
