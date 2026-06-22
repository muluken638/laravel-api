<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // 💸 TRANSFER MONEY
    public function transfer(Request $request)
    {
        $request->validate([
            'receiver_account' => 'required',
            'amount' => 'required|numeric|min:1',
        ]);

        $senderAccount = auth()->user()->account;

        $receiverAccount = Account::where('account_number', $request->receiver_account)
            ->first();

        if (!$receiverAccount) {
            return response()->json(['message' => 'Receiver not found'], 404);
        }

        if ($senderAccount->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        DB::transaction(function () use ($senderAccount, $receiverAccount, $request) {

            // ❌ deduct sender
            $senderAccount->balance -= $request->amount;
            $senderAccount->save();

            // ✅ add receiver
            $receiverAccount->balance += $request->amount;
            $receiverAccount->save();

            // 🔥 create ONE transaction record
            Transaction::create([
                'sender_account_id' => $senderAccount->id,
                'receiver_account_id' => $receiverAccount->id,
                'amount' => $request->amount,
                'description' => 'Transfer',
            ]);
        });

        return response()->json([
            'message' => 'Transfer successful'
        ]);
    }
  public function index()
{
    $accountId = auth()->user()->account->id;

    $transactions = Transaction::with(['sender.user', 'receiver.user'])
        ->where('sender_account_id', $accountId)
        ->orWhere('receiver_account_id', $accountId)
        ->latest()
        ->get()
        ->map(function ($t) use ($accountId) {

            $t->direction = $t->sender_account_id == $accountId
                ? 'debit'
                : 'credit';

            return $t;
        });

    return response()->json([
        'transactions' => $transactions
    ]);
}
}
