<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tblriskindicators extends Model
{
    protected $table = 'tblriskindicators';
    public $timestamps = false;
    protected $guarded = [];


    public function riskFactor()
    {
        return $this->belongsTo(tblriskfactors::class, 'name', 'factors');
    }
}
