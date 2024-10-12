<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class state extends Model
{
    use HasFactory;

    protected $table ='state';
    protected $fillable = [
        'email',
        'phone1',
        'phone2',
        'country',
        'state',
        'city',
        'address',
        'statename',
        'nationalcode',
        'scode',
        'category',
    ];

    protected $hidden= ['id'];
    protected $promaryKey='scode';

    public function national()
    {
        return $this->belongsTo(national::class, 'nationalcode', 'code');
    }


    //HasMany Start


    public function area()
    {
        return $this->hasMany(area::class, 'reportingcode', 'scode');
    }
    public function province()
    {
        return $this->hasMany(province::class, 'reportingcode', 'scode');
    }

    public function circuit()
    {
        return $this->hasMany(circuit::class, 'reportingcode', 'scode');
    }

    public function district()
    {
        return $this->hasMany(district::class, 'reportingcode', 'scode');
    }

    public function parish()
    {
        return $this->hasMany(parish::class, 'reportingcode', 'scode');
    }

    public function committe()
    {
        return $this->hasMany(committee::class, 'committeRefno', 'scode');
    }
    //HasMany End

}
