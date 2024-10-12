<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class committeememberpayment extends Model
{
    protected $table ='committeememberpayment';

    protected $fillable = [
        'committeRefno',
        'committename',
        'paidfor',
        'paidby',
        'parishcode',
        'parishname',
        'amount',
        'receipt',
        'paymentdate',
        'roleName',
       ];
}
