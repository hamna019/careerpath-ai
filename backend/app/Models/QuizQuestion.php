<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_text',
        'type',
        'category',
        'weight',
        'status',
        'options',
        'option_traits',
        'correct_answer'
    ];

    protected $casts = [
        'options' => 'array',
        'option_traits' => 'array',
    ];
}