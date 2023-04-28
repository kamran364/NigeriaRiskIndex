<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\PermissionName;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $guarded = [];
    public function PermissionsName(){
      return  $this->hasMany('App\PermissionName','id','permission_id');
    }
    public function PermissionsUser(){
        return  $this->hasMany('App\User','role','role_id');
    }
}
