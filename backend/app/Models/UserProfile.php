<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'profiles';

    protected $guarded = [];

    protected $casts = [
        'career_interests'    => 'array',
        'target_job_roles'    => 'array',
        'core_skills'         => 'array',
        'preferred_work_mode' => 'array',
        'date_of_birth'       => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
