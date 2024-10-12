<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class building_levy extends Model
{
    use HasFactory;
    protected $table = 'building_levy';
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
