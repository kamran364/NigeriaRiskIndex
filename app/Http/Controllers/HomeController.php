<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    public function profile(){
        $user =  User::where('id', auth('api')->user())->first();
        return response(['user' => $user],200);
    }
    public function save_profile(Request $request){
        if($request->file('passport')){
            $passport = $request->file('passport')->store('Passports');
        }else{
            $passport = auth()->user()->passport;
        }
        $data = [
            'passport' => $passport,
            'first_name' => $request->first_name,
            'last_name' =>  $request->last_name,
            'mobile' => $request->mobile,
            'gender' => $request->gender[0],
            'email' =>  $request->email,
            'address' =>  $request->address,
            'city' =>  $request->city,
            'lga' =>  $request->lga,
            'country' =>  $request->country,
            'company' =>  $request->company,
            'department' =>  $request->department,
            'phone' =>  $request->phone,
            'caddress' =>  $request->caddress,
            'ccity' =>  $request->ccity,
            'cstate' =>  $request->cstate,
            'ccountry' =>  $request->ccountry,
        ];
        $data = User::where('id', auth()->user()->id)->update($data);
        if($data){
            return redirect()->back()->with(['error' => 'Something went wrong']);
        }else{
            return redirect()->back()->with(['success' => 'Profile Updated Successfully']);
        }

    }
    public function user_list(){
//        $users =  User::where('id', auth()->user()->id)->first();
        $users =  User::leftjoin('roles', 'roles.id', '=', 'users.role')
            ->get();
//        dd($users);



//        dd($users->attributes);
//        if($users->getRoles()['users_list']){
//            $users = User::with('getRoles')->get();
//            foreach ($users as $key => $user){
//                $users[$key]['role_name'] = $user->getRoleName['role_name'];
//            }
//        }
        return response(['users' => $users],200);
    }
}
