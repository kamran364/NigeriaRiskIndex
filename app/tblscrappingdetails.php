<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tblscrappingdetails extends Model
{
    protected $table = 'tblscrappingdetails';
    public $timestamps = false;

    protected $fillable = ['state', 'country', 'newspaper', 'publisher', 'website', 'author', 'datecreated'];
}
