<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerView extends Model
{
    protected $fillable = ['user_id', 'career_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function career()
    {
        return $this->belongsTo(CareerDetails::class, 'career_id');
    }
}
