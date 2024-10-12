<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ministry extends Model
{
    use HasFactory;
    protected $table ='ministry';
    protected $fillable = [
        'ministry',
       ];
}
