<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GateTransaction extends Model
{
    protected $fillable = [
        'employee_id',
        'action'
    ];
}
