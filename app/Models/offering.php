<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class offering extends Model
{
    use HasFactory;
    protected $table = 'offering';
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
