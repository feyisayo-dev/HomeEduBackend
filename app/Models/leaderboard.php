<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class leaderboard extends Model
{

    use HasFactory;

    protected $table = 'leaderboard';
    protected $fillable = [
        'username',
        'stars',
        'class',
        'last_practice',
    ];

}
