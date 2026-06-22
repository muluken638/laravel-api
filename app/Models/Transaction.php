<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'sender_account_id',
        'receiver_account_id',
        'amount',
        'type',
        'description',
    ];

    protected $appends = ['direction'];

    // Transactions belonging to an account
    public function scopeForAccount($query, $accountId)
    {
        return $query->where(function ($q) use ($accountId) {
            $q->where('sender_account_id', $accountId)
              ->orWhere('receiver_account_id', $accountId);
        });
    }

    // Sender account
    public function sender()
    {
        return $this->belongsTo(Account::class, 'sender_account_id');
    }

    // Receiver account
    public function receiver()
    {
        return $this->belongsTo(Account::class, 'receiver_account_id');
    }

    // Debit/Credit for logged-in user
    public function getDirectionAttribute()
    {
        if (!auth()->check() || !auth()->user()->account) {
            return null;
        }

        $accountId = auth()->user()->account->id;

        return $this->sender_account_id == $accountId
            ? 'debit'
            : 'credit';
    }
}
