<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class department extends Model
{

    use HasFactory;
    protected $table = 'department';
    protected $fillable = [
        'parish_code',
        'logo',
        'name',
        'date',
        'leader',
        'team',
        'status',
    ];
}
