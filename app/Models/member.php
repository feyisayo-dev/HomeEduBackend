<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class member extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table ='member';

    protected $fillable = [

        'UserId',
        'email',
        'password',
        'sname',
        'fname',
        'mname',
        'Gender',
        'dob',
        'mobile',
        'Altmobile',
        'Residence',
        'Country',
        'State',
        'City',
        'Title',
        'dot',
        'MStatus',
        'Status',
        'ministry',
        'thumbnail',
        'regdate',
        'parishcode',
        'parishname',
        'role',
    ];


    public function children()
    {
        return $this->hasMany(children::class, 'ParentId', 'UserId');
    }
}
