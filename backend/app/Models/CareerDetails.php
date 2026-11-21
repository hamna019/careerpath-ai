<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerDetails extends Model
{
    use HasFactory;

    protected $table = 'careers';
    protected $guarded = [];

    protected $casts = [
        'skills' => 'array',
        'roadmap' => 'array',
        'courses' => 'array',
        'traits' => 'array',
    ];
}