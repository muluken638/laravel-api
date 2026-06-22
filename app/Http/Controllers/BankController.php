<?php

namespace App\Http\Controllers;

use App\Models\Bank;

class BankController extends Controller
{
    public function index()
    {
        return response()->json([
            'banks' => Bank::orderBy('name')->get()
        ]);
    }
}
