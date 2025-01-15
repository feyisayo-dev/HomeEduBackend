<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class examples extends Model
{

    use HasFactory;
    protected $table ='examples';
    protected $fillable = [
        'SubtopicId',
        'ExampleId',
        'Text',
        'Image',
        'Instruction',
       ];
}
