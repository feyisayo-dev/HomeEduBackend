<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class district extends Model
{
    use HasFactory;

    protected $table ='district';
    protected $fillable = [
        'email',
        'phone1',
        'phone2',
        'country',
        'state',
        'city',
        'address',
        'districtname',
        'reportingcode',
        'dcode',
        'category',
    ];
    protected $hidden= ['id','reportingcode'];

 //BelongTo

    //belong to
    public function national()
    {
        return $this->belongsTo(national::class, 'reportingcode','code');
    }

    public function state()
    {
        return $this->belongsTo(state::class, 'reportingcode', 'scode');
    }

    public function area()
    {
        return $this->belongsTo(area::class, 'reportingcode', 'acode');
    }

    public function province()
    {
        return $this->belongsTo(province::class, 'reportingcode', 'pcode');
    }

    public function circuit()
    {
        return $this->belongsTo(circuit::class, 'reportingcode', 'cicode');
    }

   // belong to end



    // HasMany
    public function parish()
    {
        return $this->hasMany(parish::class, 'reportingcode', 'dcode');
    }

    public function committe()
    {
        return $this->hasMany(committee::class, 'committeRefno', 'dcode');
    }
    //HasMany
}
