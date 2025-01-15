<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class topics extends Model
{

    use HasFactory;
    protected $table ='topics';
    protected $fillable = [
        'TopicId',
        'Topic',
        'Class',
        'Subject',
       ];

}
