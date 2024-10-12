<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class baptism extends Model
{
    use HasFactory;

    protected $table = 'baptism';  // Add this line

    protected $fillable = [
        'UserId',
        'Status',
        'Amount',
        'Year_of_Joining',
    ];
}
