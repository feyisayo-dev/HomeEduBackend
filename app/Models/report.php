<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class report extends Model
{

    use HasFactory;
    protected $table = 'reports';
    protected $fillable = [
        'username',
        'SubtopicId',
        'ExamId',
        'Score',
        'time_taken',
    ];
}
