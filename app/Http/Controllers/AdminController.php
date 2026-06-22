<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;

class AdminController extends Controller
{
    // 📊 Admin dashboard summary
    public function dashboard()
    {
        return response()->json([
            "total_users" => User::count(),
            "total_transactions" => Transaction::count(),
            "total_volume" => Transaction::sum('amount'),
        ]);
    }

    // 👥 All users
    public function users()
    {
        return response()->json([
            "users" => User::with('account')->latest()->get()
        ]);
    }

    // 💰 All transactions (system-wide)
    public function transactions()
    {
        return response()->json([
            "transactions" => Transaction::with(['senderAccount', 'receiverAccount'])
                ->latest()
                ->get()
        ]);
    }
}
