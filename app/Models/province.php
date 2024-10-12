<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class province extends Model
{
    use HasFactory;
    protected $table ='province';
    protected $fillable = [
        'email',
        'phone1',
        'phone2',
        'country',
        'state',
        'city',
        'address',
        'provincename',
        'reportingcode',
        'pcode',
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


       // belong to end

    //Hasmany
    public function circuit()
    {
        return $this->hasMany(circuit::class, 'reportingcode', 'pcode');
    }

    public function district()
    {
        return $this->hasMany(district::class, 'reportingcode', 'pcode');
    }

    public function parish()
    {
        return $this->hasMany(parish::class, 'reportingcode', 'dcode');
    }

    public function committe()
   {
       return $this->hasMany(committee::class, 'committeRefno', 'pcode');
   }
    //Hasmany




}
