<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class committeemember extends Model
{
    use HasFactory;
    protected $table ='committeemembers';

    protected $fillable = [
        'committeRefno',
        'committeName',
        'chairman',
        'chairperson',
        'secretary',
        'Fsecretary',
        'treasurer',
        'Mmembers',
        'Fmembers',
       ];

}
