<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class adultharvest extends Model
{
    use HasFactory;

    protected $table ='adultharvest';
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
