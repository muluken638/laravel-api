<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name'   => 'required|string|max:255',
            'middle_name'  => 'nullable|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|string|email|unique:users,email',
            'phone_number' => 'required|string|unique:users,phone_number',
            'password'     => 'required|string|min:6',
        ]);

        DB::beginTransaction();

        try {

            // 1. Create user
            $user = User::create([
                'first_name'   => $request->first_name,
                'middle_name'  => $request->middle_name,
                'last_name'    => $request->last_name,
                'email'        => $request->email,
                'phone_number' => $request->phone_number,
                'password'     => Hash::make($request->password),
            ]);

            // 2. Create account automatically
            $account = $user->account()->create([
                'account_number' => $this->generateAccountNumber(),
                'bank_name'      => 'Default Bank',
                'balance'        => 0,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
                'account' => $account
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = auth()->user()->load('account');

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => trim($user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name),
                'email' => $user->email,
                'phone_number' => $user->phone_number,

                'account_number' => $user->account?->account_number,
                'bank_name'      => $user->account?->bank_name,
                'balance'        => $user->account?->balance,
                'role' => $user->role,
            ]
        ]);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to logout'
            ], 500);
        }
    }

    /**
     * Generate 10-digit sequential account number starting from 1000000001
     */
    private function generateAccountNumber()
    {
        return DB::transaction(function () {

            $last = \App\Models\Account::lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            $startNumber = 1000000000;

            if (!$last) {
                return (string) ($startNumber + 1); // 1000000001
            }

            return (string) ((int) $last->account_number + 1);
        });
    }
}
