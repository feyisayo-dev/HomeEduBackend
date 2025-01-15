<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exams extends Model
{

    use HasFactory;

    protected $table ='Exams';
    protected $fillable = [
        'SubjectId',
        'Exam',
        'ExamId',
       ];
}
