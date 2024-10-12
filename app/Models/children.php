<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class children extends Model
{
    use HasFactory;
    protected $table ='children';

    protected $fillable = [
        'ParentId',
        'password',
        'email',
        'sname',
        'fname',
        'mname',
        'Gender',
        'dob',
       ];


       public function member()
       {
           return $this->belongsTo(member::class, 'ParentId', 'UserId');
       }

       protected $hidden= ['id'];

}

