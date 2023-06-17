<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $credentials = $request->only('email', 'password');
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error(
                    'Unauthorized',
                    401
                );
            }

            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid Password');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Login Successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'string', 'unique:users'],
                'password' => ['required', 'string', new Password]
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Register Successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success([
            'access_token' => $token,
            'user' => $user
        ], 'Logout Successfully');
    }

    public function fetch(Request $request)
    {
        $user = Auth::user();

        return ResponseFormatter::success($user, 'Fetch Success');
    }
}
