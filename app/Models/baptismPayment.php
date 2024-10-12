<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class baptismPayment extends Model
{
    use HasFactory;
    protected $table = 'baptism_payment';
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
