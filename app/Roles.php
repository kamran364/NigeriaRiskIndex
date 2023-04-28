<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $table = 'roles';
    public function get_role_list(){
        return Roles::rightjoin('permissions','permissions.role_id','=','roles.id')
//            ->select('')
            ->get();
    }
}
