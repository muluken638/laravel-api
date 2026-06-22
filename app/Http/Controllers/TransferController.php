<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    /**
     * Verify receiver account
     */
    public function verifyAccount(Request $request)
    {
        $request->validate([
            'account_number' => 'required'
        ]);

        $account = Account::with('user')
            ->where('account_number', $request->account_number)
            ->first();

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'account_name' => trim(
                ($account->user?->first_name ?? '') . ' ' .
                    ($account->user?->middle_name ?? '') . ' ' .
                    ($account->user?->last_name ?? '')
            ),
            'account_number' => $account->account_number,
            'bank_id' => $account->bank_id ?? null
        ]);
    }

    /**
     * Transfer money
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'receiver_account' => 'required',
            'amount' => 'required|numeric|min:1'
        ]);

        DB::beginTransaction();

        try {

            $senderAccount = Account::lockForUpdate()
                ->find(auth()->user()->account->id);

            $receiverAccount = Account::lockForUpdate()
                ->where(
                    'account_number',
                    $request->receiver_account
                )
                ->first();

            if (!$receiverAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiver account not found'
                ], 404);
            }

            // Prevent self-transfer
            if ($senderAccount->id == $receiverAccount->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot transfer to your own account'
                ], 400);
            }

            // Same bank validation
            if ($senderAccount->bank_id != $receiverAccount->bank_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only same-bank transfers are allowed'
                ], 400);
            }

            if ($senderAccount->balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance'
                ], 400);
            }

            // Debit sender
            $senderAccount->decrement(
                'balance',
                $request->amount
            );

            // Credit receiver
            $receiverAccount->increment(
                'balance',
                $request->amount
            );

            Transaction::create([
                'sender_account_id' => $senderAccount->id,
                'receiver_account_id' => $receiverAccount->id,
                'amount' => $request->amount,
                'type' => 'debit',
                'description' =>
                'Same bank transfer from '
                    . $senderAccount->account_number
                    . ' to '
                    . $receiverAccount->account_number,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transfer successful'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
 * Verify receiver by phone number
 */
public function verifyPhone(Request $request)
{
    $request->validate([
        'phone_number' => 'required'
    ]);

    $account = Account::with('user')
        ->whereHas('user', function ($query) use ($request) {
            $query->where('phone_number', $request->phone_number);
        })
        ->first();

    if (!$account) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'account_name' =>
            ($account->user->first_name ?? '') . ' ' .
            ($account->user->middle_name ?? '') . ' ' .
            ($account->user->last_name ?? ''),

        'account_number' => $account->account_number,
        'phone_number' => $account->user->phone_number
    ]);
}
/**
 * Transfer using phone number
 */
public function transferByPhone(Request $request)
{
    $request->validate([
        'phone_number' => 'required',
        'amount' => 'required|numeric|min:1'
    ]);

    DB::beginTransaction();

    try {

        // sender
        $senderAccount = Account::lockForUpdate()
            ->find(auth()->user()->account->id);

        // receiver by phone
        $receiverAccount = Account::lockForUpdate()
            ->whereHas('user', function ($query) use ($request) {
                $query->where('phone_number', $request->phone_number);
            })
            ->first();

        if (!$receiverAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Receiver not found'
            ], 404);
        }

        // self transfer check
        if ($senderAccount->id == $receiverAccount->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot transfer to yourself'
            ], 400);
        }

        // same bank check
        if ($senderAccount->bank_id != $receiverAccount->bank_id) {
            return response()->json([
                'success' => false,
                'message' => 'Only same-bank transfers allowed'
            ], 400);
        }

        // balance check
        if ($senderAccount->balance < $request->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance'
            ], 400);
        }

        // update balances
        $senderAccount->decrement('balance', $request->amount);
        $receiverAccount->increment('balance', $request->amount);

        Transaction::create([
            'sender_account_id' => $senderAccount->id,
            'receiver_account_id' => $receiverAccount->id,
            'amount' => $request->amount,
            'type' => 'debit',
            'description' => 'Transfer via phone number',
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Transfer successful'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}
