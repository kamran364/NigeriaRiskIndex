<?php

namespace App\Http\Controllers;

use App\Permission;
use App\ResetPassword;
use App\tblweeklydataentry;
use App\Utils\Constant;
use App\Utils\Error;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Lcobucci\JWT\Token;
use \Illuminate\Support\Facades\Mail;
use Response;

class UserController extends Controller
{
    public function forgetPassword(Request $request) {
        try {
            $user = User::where('email', $request->input('email'))->first();
            if(!$user)
                return response(['error' => Constant::EmailNotExist],403);

            $token = ResetPassword::create([
                  'email' => $user->email
                , 'token' => uniqid()
                , 'expires_at' => Carbon::now()->addHour()
                , 'created_at' => Carbon::now()
            ]);
            $email = self::sentEmail($user, $token, $request);
            if($email)
                return response(['message' => Constant::EmailSent],200);
            else
                return response(['error' => $email->getMessage()], 403);

        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()], 403);
        }

    }
    public static function sentEmail($user, $token, $request) {
        try {
            $subject = 'Forget Password';
            $url = env('APP_URL');
            Mail::send('mails.forget-password', ['token' => $token, 'request' => $request, 'url' =>  $url], function($message) use($user, $subject, $token, $request) {
                $message->to($user['email'], $user['name'])->subject($subject);
            });
            return true;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function reset_password($token){
        $dbToken = ResetPassword::where('token', $token)
            ->where('expires_at', '>' , Carbon::now())
            ->first();
        if(!$dbToken){
            return redirect('/');
        }
        return redirect('/#/reset_password?t='. $token);
    }
    public function resetPassword(Request $request){

        $token = $request->input('token');
        $dbToken = ResetPassword::where('token', $token)->first();
        if(!$dbToken){
            return response(['error' => 'Token expires or not exist'],403);
        }
        $user = User::where('email', $dbToken->email)->first();
        $user->password = bcrypt($request->input('password'));
        $user->save();
        return response(['data' => 'Password has been updated'],200);

    }
    public function save_profile(Request $request){
        if($request->file('passport')){
            $passport = $request->file('passport')->store('Passports');
        }else{
            $passport = $request->passport;
        }
        $data = [
            'passport' => $passport,
            'first_name' => $request->first_name,
            'last_name' =>  $request->last_name,
            'mobile' => $request->mobile,
            'gender' => $request->gender,
            'email' =>  $request->email,
            'address' =>  $request->address,
            'city' =>  $request->city,
            'state' =>  $request->state,
            'lga' =>  $request->lga,
            'country' =>  'Nigeria',
            'company' =>  $request->company,
            'department' =>  $request->department,
            'phone' =>  $request->phone,
            'caddress' =>  $request->caddress,
            'cname' =>  $request->cname,
            'ccity' =>  $request->ccity,
            'cstate' =>  $request->cstate,
            'ccountry' =>  'Nigeria',
        ];
//        return response(['data' => auth('api')->user()->id],200);
        $data = User::where('id', auth('api')->user()->id)->update($data);
        if(!$data){
            return response(['data' => 'Something went wrong'],500);
        }else{
            return response(['data' => 'Profile Updated Successfully'],200);
        }
    }
    public function add_user(Request $request){
        $data = [
            'first_name' => $request->first_name,
            'last_name' =>  $request->last_name,
            'mobile' => $request->mobile,
            'gender' => $request->gender,
            'email' =>  $request->email,
            'address' =>  $request->address,
            'city' =>  $request->city,
            'role' => $request->roles,
            'password' => bcrypt($request->password),
            'end_trail' => $request->trail,
        ];
        $data = User::insert($data);
        if(!$data){
            return response(['data' => 'Something went wrong'],500);
        }else{
            return response(['data' => 'New User Added Successfully'],200);
        }
    }
    public function getUserData($id){
        return User::where('id', $id)->with('getRoleName')->first();
    }
    public function updateUser(Request $request){
        $data = [
            'first_name' => $request->first_name,
            'last_name' =>  $request->last_name,
            'mobile' => $request->mobile,
            'gender' => $request->gender,
            'email' =>  $request->email,
            'address' =>  $request->address,
            'city' =>  $request->city,
            'role' => $request->role,
            'deactivated' => $request->deactivated,
        ];
        if($request->password){
            $data['password'] = bcrypt($request->password);
        }
        if($request->trail){
            $data['end_trail'] = $request->trail;
        }
        return User::where('id', $request->id)->update($data);
    }
    public function validate_email(Request $request){
        $email =  $request->input('email');
        $user_obj =  new User();
        $get_data =  $user_obj->validate_email($email);
        if($get_data){
            return response(['status' => false]);
        }
        return response(['status' => true]);
    }
    public function migrate() {

        $tblusers = DB::table('tblusers')->join('tbllogin', 'tbllogin.usernamerisk' ,'=','tblusers.usernamerisk')->get();
//        dd($tblusers);
        $count = 0;
        foreach($tblusers as $user){
            $user_data = [
                'first_name' => $user->fname,
                'last_name' => $user->lname,
                'email' => $user->usernamerisk,
                'password' => bcrypt($user->password),
                'role' => $user->role,
//                'end_trail' => '',
//                'mobile' => $user->phone,
                'gender' => $user->gender,
                'department' => $user->department,
//                'company' => '',
                'phone' => $user->phone,
                'address' => $user->address,
                'mailbag' => $user->mailbag,
//                'passport' => '',
                'lga' => $user->lga,
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country,
                'cname' => $user->cname,
                'ccity' => $user->ccity,
                'cstate' => $user->cstate,
                'caddress' => $user->caddress,
                'ccountry' => $user->ccountry,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
            $user_obj = User::insert($user_data);
            if($user_obj)
                $count++;

        }
        dd($count . " users Migrated Successfully");
    }
    public function SetImagePath() {
        ini_set('max_execution_time',0);
        foreach(tblweeklydataentry::get() as $incident){
//            if(strpos($incident->image, 'me') == false)
               tblweeklydataentry::where(['eventid' => $incident->eventid])->update(['image' => "storage/app/images/incidents/".$incident->eventid.'.png']);
        }
    }
    /*function to set roles in DB*/
    public function test()
    {
        dd('Hassan');
//        $user = User::where(['id'=>8])->first();
//        dd($user->getRoleName->role_name);
//        dd($user);
        $data_officers = [8, 19, 5, 27, 28, 30, 18, 31, 17, 12, 6];
        $security_analyst = [8, 18, 31, 17, 12, 6, 20];
        $external_partner = [17];
        $subscriber = [7, 8, 5, 12, 6, 24];
        foreach ($subscriber as $key) {
            $array = ['role_id' => 10, 'permission_id' => $key, 'status' => 1];
            Permission::insert($array);
        }
    }
}
