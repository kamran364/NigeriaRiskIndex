<?php

namespace App\Http\Controllers;

use App\Permission;
use App\PermissionName;
use Illuminate\Http\Request;
use App\Roles;
use Auth;
use Illuminate\Support\Facades\Cookie;


class RoleController extends Controller
{
    public function get_role(){
        return response(['roles' => Roles::where('id','<>',1)->get()],200);
    }
    public function get_permission(){
        return response(['permissions' => PermissionName::get()],200);
    }
    public function get_role_permission(Request $request){
        $id = $request->input('id');
        $permissions = Permission::where('role_id', $id)->with(['PermissionsName'])->get();
        return response(['permissions' => $permissions],200);
    }

    public function roles(Request $request){
//        dd(Auth::user());
        return view('roles',['roles' => Roles::where('id','<>',1)->get(), 'permissions' => PermissionName::get()->sortBy('group_id')]);
    }
    public function update_roles(Request $request){
        try{
            $role = $request->input('role_id');
            $permission = $request->input('permission');
            foreach ($permission as $per){
                Permission::updateOrCreate(['role_id' => $role, 'permission_id' => $per],['status' => 1]);
            }
            return 1;
        }catch(Exception $e){
            return 0;
        }


    }
}
