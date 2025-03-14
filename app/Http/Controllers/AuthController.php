<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request){
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);


            return response()->json(['message' => 'User created successfully'], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }

    public function login(Request $request){
        try {
            $request->validate([
                'email' => 'required|string|email|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('email', 'password');

            if (!auth()->attempt($credentials)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = auth()->user();
            $token = $user->createToken('Laravel Passport')->accessToken;

            return response()->json(['token' => $token], 200);
        }catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }
    
    public function logout(){
        try {
            
            auth()->user()->token()->revoke();

            return response()->json(['message' => 'Logged out successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }
}