<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * Display a listing of banks.
     */
    public function index()
    {
        return response()->json([
            'banks' => Bank::orderBy('name')->get()
        ]);
    }

    /**
     * Store a newly created bank.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:banks,name',
        ]);

        $bank = Bank::create($validated);

        return response()->json([
            'message' => 'Bank created successfully.',
            'bank' => $bank
        ], 201);
    }

    /**
     * Display the specified bank.
     */
    public function show(Bank $bank)
    {
        return response()->json([
            'bank' => $bank
        ]);
    }

    /**
     * Update the specified bank.
     */
    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:banks,name,' . $bank->id,
        ]);

        $bank->update($validated);

        return response()->json([
            'message' => 'Bank updated successfully.',
            'bank' => $bank->fresh()
        ]);
    }

    /**
     * Remove the specified bank.
     */
    public function destroy(Bank $bank)
    {
        $bank->delete();

        return response()->json([
            'message' => 'Bank deleted successfully.'
        ]);
    }
}
