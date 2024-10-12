<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class announcement extends Model
{

    use HasFactory;
    protected $table = 'announcement';
    protected $fillable = [
        'parish_code',
        'title',
        'message',
        'date',
    ];
}
