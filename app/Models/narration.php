<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class narration extends Model
{

    use HasFactory;
    protected $table = 'narrations';
    protected $fillable = [
        'SubtopicId',
        'Content',
        'QuestionId',
        'NarrationId',
    ];
}
