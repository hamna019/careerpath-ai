<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentStudentActivity extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'score',
        'started_at',
        'completed_at',
        'last_activity_at',
    ];

    protected $casts = [
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
