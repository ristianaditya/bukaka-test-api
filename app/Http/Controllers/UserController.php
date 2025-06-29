<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'string|between:2,100',
            'email'     => 'required|email|unique:users',
            'role' => 'in:admin,user',
            'password'  => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)],
        ));

        if($user) {
            return response()->json([
                'success' => true,
                'message' => 'User successfully registered',
            ], 201);
        }

        return response()->json([
            'success' => false,
        ], 409);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required',
            'password'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if(!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'email or password is incorrect'
            ], 401);
        }

        $user = auth()->guard('api')->user();
        $user = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'no_phone' => $user->no_phone,
            'sim' => $user->sim,
        ];

        $payload = JWTAuth::setToken($token)->getPayload();
        $expirationTime = $payload->get('exp');

        return response()->json([
            'success' => true,
            'data'    => $user,
            'expires_in' => $expirationTime,
            'access_token'   => $token,
        ], 200)
        ->header('Access-Control-Allow-Origin', 'http://localhost:3000')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');
    }

    public function logout()
    {
        try {
            $removeToken = JWTAuth::invalidate(JWTAuth::getToken());
            if($removeToken) {
                return response()->json([
                    'success' => true,
                    'message' => 'User successfully signed out',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 409);
        }
    }

    public function getSession()
    {
        try {
            $userToken = JWTAuth::parseToken()->authenticate();

            $user = User::find($userToken->id);

            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 401);
        }
    }

    public function user(Request $request){
        try {
            return $request->user();
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 401);
        }
    }

    public function refreshToken(){
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            if($newToken) {
                $payload = JWTAuth::setToken($newToken)->getPayload();
                $expirationTime = $payload->get('exp');
                return response()->json([
                    'success' => true,
                    'message' => 'Token successfully refresh',
                    'access_token' => $newToken,
                    'expires_in' => $expirationTime,
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 401);
        }
    }

    public function getUser(){
        return response()->json([
            'success' => true,
            'data' => User::all()
        ]);
    }

    public function showUser(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|between:2,100',
            'email' => 'email|unique:users,email,'.$user->id,
            'no_phone' => 'between:6,15|unique:users,no_phone,'.$user->id,
            'sim' => 'between:14,15|unique:users,sim,'.$user->id,
            'address' => 'string|between:2,200',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user->update($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function updatePassword(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password'  => 'required|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            $user = JWTAuth::parseToken()->authenticate();
            if(Hash::check($request->old_password, $user->password, [])) {
                $user->update(['password' => bcrypt($request->password)]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'password failed to update',
                ], 422);
            }
            return response()->json([
                'success' => true,
                'data' => 'password successfully updated'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 401);
        }

    }

    public function destroy($id)
    {
        $users = User::find($id);
        if (!$users) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $users->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
