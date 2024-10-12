<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class juvelineharvest extends Model
{
    use HasFactory;

    protected $table ='juvelineharvest';
    protected $fillable = [
        'UserId',
        'FullName',
        'pymtdate',
        'Amount',
        'parishcode',
        'parishname',
        'pymtImg',
       ];
}
