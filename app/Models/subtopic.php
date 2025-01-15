<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class subtopic extends Model
{

    use HasFactory;

    protected $table ='subtopics';
    protected $fillable = [
        'TopicId',
        'Subtopic',
        'SubtopicId',
       ];
}
