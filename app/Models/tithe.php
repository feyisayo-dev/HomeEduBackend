<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tithe extends Model
{
    use HasFactory;
    protected $table = 'tithe';
    protected $fillable = [
        'pymtdate',
        'Amount',
        'parishcode',
        'parishname',
        'receipt',
        'paidby',
        'paidfor',
    ];
}
