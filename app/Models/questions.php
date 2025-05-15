<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class questions extends Model
{

    use HasFactory;
    protected $table ='questions';
    protected $fillable = [
        'QuestionId',
        'type',
        'content',
        'options',
        'answer',
        'class',
        'subtopic',
        'subject',
        'topic',
        'image',
       ];
}
