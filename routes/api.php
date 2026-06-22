<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\GateController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'role:admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);

    Route::get('/admin/users', [AdminController::class, 'users']);

    Route::get('/admin/transactions', [AdminController::class, 'transactions']);

});
Route::middleware('auth:api')->group(function () {

    // auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // profile
    Route::get('/me', function () {
        $user = auth()->user()->load('account');

        return response()->json([
            "user" => [
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role, // 🔥 IMPORTANT
                "account_number" => $user->account->account_number ?? null,
                "bank_name" => $user->account->bank_name ?? null,
                "balance" => $user->account->balance ?? 0,
            ]
        ]);
    });

    // transfers (ONLY ONE SYSTEM)
    Route::post('/transfer/verify-account', [TransferController::class, 'verifyAccount']);
    Route::post('/transfer', [TransferController::class, 'transfer']);
    Route::post('/transfer-phone', [TransferController::class, 'transferByPhone']);
    Route::post('/verify-phone', [TransferController::class, 'verifyPhone']);

    // transactions
    Route::get('/transactions', [TransactionController::class, 'index']);

    // banks
    Route::get('/banks', [BankController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| GATE SYSTEM (public or secured depending on logic)
|--------------------------------------------------------------------------
*/
Route::post('/scan', [GateController::class, 'scan']);
