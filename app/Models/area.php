<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class area extends Model
{
    use HasFactory;
    protected $table ='area';
    protected $fillable = [
        'email',
        'phone1',
        'phone2',
        'country',
        'state',
        'city',
        'address',
        'areaname',
        'reportingcode',
        'acode',
        'category',
    ];
    // protected $hidden= ['id','reportingcode'];

//belong to
public function national()
{
    return $this->belongsTo(national::class, 'reportingcode', 'code');
}
public function state()
{
    return $this->belongsTo(state::class, 'reportingcode', 'scode');
}
//Belong To


//Hasmany
public function province()
{
    return $this->hasMany(province::class, 'reportingcode', 'acode');
}

public function circuit()
{
    return $this->hasMany(circuit::class, 'reportingcode', 'acode');
}

public function district()
{
    return $this->hasMany(district::class, 'reportingcode', 'acode');
}

public function parish()
{
    return $this->hasMany(parish::class, 'reportingcode', 'acode');
}

public function committe()
{
    return $this->hasMany(committee::class, 'committeRefno', 'acode');
}
//Hasmany end

}
