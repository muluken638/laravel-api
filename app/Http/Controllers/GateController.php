<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\GateTransaction;
use Illuminate\Http\Request;

class GateController extends Controller
{
    public function scan(Request $request)
    {
        $employee = Employee::where('qr_code', $request->qr)->first();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid QR'
            ]);
        }

        $last = GateTransaction::where('employee_id', $employee->id)
            ->latest()->first();

        $action = (!$last || $last->action == 'OUT') ? 'IN' : 'OUT';

        GateTransaction::create([
            'employee_id' => $employee->id,
            'action' => $action
        ]);

        return response()->json([
            'status' => true,
            'message' => $employee->name,
            'action' => $action
        ]);
    }
}
