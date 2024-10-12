<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class circuit extends Model
{
    use HasFactory;

    protected $table ='circuit';
    protected $fillable = [
        'email',
        'phone1',
        'phone2',
        'country',
        'state',
        'city',
        'address',
        'circuitname',
        'reportingcode',
        'cicode',
        'category',
    ];
    protected $hidden= ['id','reportingcode'];


    //belong to
    public function national()
    {
        return $this->belongsTo(national::class, 'reportingcode', 'code');
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
   // belong to end


   //Has Many
   public function district()
   {
       return $this->hasMany(district::class, 'reportingcode', 'cicode');
   }

   public function parish()
   {
       return $this->hasMany(parish::class, 'reportingcode', 'cicode');
   }

   public function committe()
   {
       return $this->hasMany(committee::class, 'committeRefno', 'cicode');
   }
   //Has Many end


}
