<?php

namespace App\Http\Controllers;

use App\User;
use App\Utils\Constant;
use App\Utils\Error;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;
use Mockery\Exception;


class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        return response()->json(['user' => $user]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        try {
            $user = User::where('email', request('email'))->first();
            if (!$user)
                return response(['error' => Constant::EmailNotFound], 422);

            if ($user->deactivated)
                return response(['error' => Constant::AcntNtAcvt], 403);

            if (self::isValidUser($user))
                return response(['error' => Constant::TrialExpire], 403);

            $credentials = request(['email', 'password']);
            if (!$token = auth('api')->attempt($credentials, ['exp' => Carbon::now()->addDays(7)->timestamp]))
                return response(['error' => Constant::InvalidPassword], 422);

            session(['user' => $this->guard()->user()]);
            cookie('user', $this->guard()->user());
            Auth::login($this->guard()->user());
            return $this->respondWithToken($token);

        } catch (\Exception $e) {

            return response(['error' =>  Constant::BadRequest]);
        }

    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function isValidUser($user) {
        return ($user->role == 3 && Carbon::parse($user->end_trail)->format('Y-m-d') < Carbon::now()->format('Y-m-d'));
    }
    public function me()
    {
        return response()->json(['user' => auth('api')->user(),
            'permission' => auth('api')->user()->getRoles(),
            'role' => strtoupper(auth('api')->user()->getRoleName->role_name)]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'user' => $this->guard()->user(),
            'permissions' => $this->guard()->user()->getRoles(),
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 600,
            'role' => strtoupper(auth('api')->user()->getRoleName->role_name)
        ]);
    }

    public function guard()
    {
        return \Auth::Guard('api');
    }
}
