<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{

    use HasFactory;
    protected $table ='students';
    protected $fillable = [
        'username',
        'fullName',
        'dob',
        'email',
        'password',
        'phoneNumber',
        'class',
        'parentName',
        'parentContact',
        'address',
        'thumbnail ',
        'role',
       ];
}
