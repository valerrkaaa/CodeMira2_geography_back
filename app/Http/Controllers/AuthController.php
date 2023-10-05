<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'userRoles', 'me']]);
    }

    public function me(Request $request)
    {
        if (Auth::user())
            return response()->json(['status' => 'success']);
        else
            return response()->json(['status' => 'not exist']);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validator error',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wrong password',
            ], 401);
        }

        $user = Auth::user();
        $role = UserRole::find($user->role_id)->role;
        return response()->json([
            'status' => 'success',
            'body' => [
                'user' => $user,
                'role' => $role,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]
        ]);

    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|between:2,100|unique:users,name',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'role' => 'required|string|exists:user_roles,role'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validator error',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $role_id = UserRole::where('role', $request->role)->first()->id;

        $user = User::create([
            'name' => $request->login,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role_id
        ]);

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'body' => [
                'message' => 'User created successfully',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function userRoles(){
        $roleList = [];
        $roles = UserRole::select('role')->get();
        foreach ($roles as $role){
            array_push($roleList, $role['role']);
        }
        return response()->json(['status' => 'success', 'roles' => $roleList]);
    }

    public function getUser(){

        if (Auth::user())
            return response()->json(['status' => 'success', 'user' => User::find(auth()->id())->first()]);
        else
            return response()->json(['status' => 'not exist']);
    }
}