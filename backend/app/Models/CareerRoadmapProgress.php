<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerRoadmapProgress extends Model
{
    protected $table = 'career_roadmap_progress';

    protected $fillable = [
        'user_id',
        'career_id',
        'step_index',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function career()
    {
        return $this->belongsTo(CareerDetails::class, 'career_id');
    }
}
