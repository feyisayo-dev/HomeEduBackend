<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class national extends Model
{
    use HasFactory;

    protected $table ='national';
    protected $fillable = [
        'email',
        'phone1',
        'phone2',
        'country',
        'states',
        'city',
        'address',
        'nationalname',
        'code',
        'category',
    ];

    public function state()
    {
        return $this->hasMany(state::class, 'nationalcode', 'code');
    }



    public function area()
    {
        return $this->hasMany(area::class, 'reportingcode', 'code');
    }
    public function province()
    {
        return $this->hasMany(province::class, 'reportingcode', 'code');
    }

    public function circuit()
    {
        return $this->hasMany(circuit::class, 'reportingcode', 'code');
    }

    public function district()
    {
        return $this->hasMany(district::class, 'reportingcode', 'code');
    }

    public function parish()
    {
        return $this->hasMany(parish::class, 'reportingcode', 'code');
    }

    public function committe()
    {
        return $this->hasMany(committee::class, 'committeRefno', 'code');
    }

}
