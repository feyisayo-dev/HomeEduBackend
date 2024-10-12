<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class committee extends Model
{
    use HasFactory;
    protected $table ='committee';
    protected $fillable = [
        'committeRefno',
        'committeName',
        'parishcode',
        'parishname',
        'from',
        'to',
    ];


    //belong to
public function national()
{
    return $this->belongsTo(national::class, 'parishcode', 'code');
}

public function state()
{
    return $this->belongsTo(state::class, 'parishcode', 'scode');
}

public function area()
{
    return $this->belongsTo(area::class, 'parishcode', 'acode');
}

public function province()
{
    return $this->belongsTo(province::class, 'parishcode', 'pcode');
}

public function circuit()
{
    return $this->belongsTo(circuit::class, 'parishcode', 'cicode');
}

public function district()
{
    return $this->belongsTo(district::class, 'parishcode', 'dcode');
}

public function parish()
{
    return $this->belongsTo(parish::class, 'parishcode', 'picode');
}


//has many

public function committemember()
{
    return $this->hasMany(committemember::class, 'committeRefno', 'committeRefno');
}





}
