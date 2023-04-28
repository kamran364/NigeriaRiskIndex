<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tbldataentry extends Model
{
    protected $table = 'tbldataentry';

    public $timestamps = false;
    protected $guarded = [];
    public function save_data($data){
        return tbldataentry::insert($data);
    }
}
