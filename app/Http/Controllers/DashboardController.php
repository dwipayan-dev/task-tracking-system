<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_employee' => 0,
            'total_task' => 0,
            'total_employee_present' => 0,
            'total_employee_absent' => 0,
        ];
        $data['total_employee'] = User::where('id', '!=', 1)->where('status', 1)->count();
        $data['total_task'] = Task::whereDate('created_at', Carbon::today())->count();
        $data['total_employee_present'] = Attendance::whereDate('created_at', Carbon::today())->pluck('user_uuid')->unique()->count();
        $data['total_employee_absent'] = $data['total_employee'] - $data['total_employee_present'];
        return response()->json([
            'status' => "success",
            'result' => $data
        ], 200);
    }
}
