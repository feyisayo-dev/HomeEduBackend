<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vineyard extends Model
{
    use HasFactory;
    protected $table ='vineyard';
    protected $fillable = [
        'vineyard',
       ];
}
