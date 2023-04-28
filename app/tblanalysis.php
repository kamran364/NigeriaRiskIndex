<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tblanalysis extends Model
{
    protected $table = 'tblanalysis';
    public $timestamps = false;
    protected $fillable = ['reportdate','caption','content','location','dweek','dyear','analysisid','type','lga','riskfactor','riskindicator'];
}
