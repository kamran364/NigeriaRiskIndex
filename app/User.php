<?php

namespace App;

use Illuminate\Notifications\Notifiable;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','first_name','last_name','email', 'password','role','end_trail','mobile','gender','department','company_name','phone','address','mailbag','city','state','country','deactivated'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.

     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function ResetPassword(){
        return $this->hasMany('App\ResetPassword');
    }
    public function validate_email($email){
        return User::where('email', $email)->first();
    }
    public function getRoles() {
       //Get Permission Lists..
        $permission =  User::join('roles','roles.id','=','role')
            ->join('permissions','permissions.role_id','=','roles.id')
            ->join('permissions_name','permissions.permission_id','=','permissions_name.id')
            ->where('users.id',auth('api')->user()->id)
            ->pluck('permissions.status','permissions_name.name');

        return array_add($permission, 'user_profile', 1);
    }
    public function getRoleName(){
        return $this->hasOne('App\Roles','id','role');
    }
    public function PermissionsUser(){
        return  $this->hasMany('App\Permission','role_id','role');
    }

    public static function canUserAccess(){
        try{
            $permission =  User::rightjoin('roles','roles.id','=','role')
                ->join('permissions','permissions.role_id','=','roles.id')
                ->join('permissions_name','permissions.permission_id','=','permissions_name.id')
                ->where('users.role', auth()->user()->role)
                ->where('permissions_name.name', 'manage_roles')
                ->pluck('permissions.status','permissions_name.name');
//            ->toSql();
//        dd($permission);
            if($permission && $permission['manage_roles']){
                return true;
            }else{
                return false;
            }
        }catch (\Exception $e){
            return false;
        }

    }

}
