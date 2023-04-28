<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tblriskfactors extends Model
{
    protected $table = 'tblriskfactors';
    public $timestamps = false;
    protected $guarded = [];

    // Get Risk Indicators of each risk factors
    public function getRiskIndicators()
    {
    	return $this->hasMany(tblriskindicators::class, 'factors', 'name');
    }
}
