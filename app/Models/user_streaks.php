<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_streaks extends Model
{
    use HasFactory;
    protected $table = 'user_streaks';

    protected $fillable = ['username', 'last_practice_date', 'streak_count'];
}
