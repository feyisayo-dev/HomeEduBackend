<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class explanations extends Model
{

    use HasFactory;

    protected $table = 'explanations';
    protected $fillable = [
        'SubtopicId',
        'ExplanationId',
        'Content',
    ];
}
