<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCareerResult extends Model
{
    protected $table = 'students_careers_analytics';

    protected $fillable = [
        'user_id',
        'career_id',
        'career_title',
        'score',
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
