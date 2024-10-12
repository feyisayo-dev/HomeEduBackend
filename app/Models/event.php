<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class event extends Model
{
    use HasFactory;


    protected $table = 'event';

    protected $fillable = [
        'EventId',
        'Title',
        'Description',
        'startdate',
        'enddate',
        'Time',
        'Moderator',
        'end_time',
        'start_time',
        'Minister',
        'guest',
        'location',
        'Type',
        'parishcode',
        'parishname',
        'eventImg',
    ];
}
