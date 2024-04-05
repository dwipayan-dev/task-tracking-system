<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTaskActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function task_log(Request $request)
    {
        $user_activity = UserTaskActivity::query();
        $user_activity = $user_activity->with(['getEmployee', 'getTask']);
        if ($request->search != 'null') {
            $employee_uuid = User::where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('position', 'like', '%' . $request->search . '%')
                ->orWhere('employee_id', 'like', '%' . $request->search . '%')->pluck('uuid')->toArray();
            if (count($employee_uuid) > 0)
                $user_activity = $user_activity->whereIn('user_uuid', $employee_uuid);
            $task_uuid = Task::where('task_name', 'like', '%' . $request->search . '%')
                ->orWhere('project_name', 'like', '%' . $request->search . '%')
                ->pluck('uuid')->toArray();
            if (count($task_uuid) > 0)
                $user_activity = $user_activity->whereIn('task_uuid', $task_uuid);
        }
        // return $employee_uuid;
        if ($request->date != 'null')
            $user_activity = $user_activity->whereDate('created_at', $request->date);
        if ($request->date == 'null' && $request->search == 'null')
            $user_activity = $user_activity->whereDate('created_at', Carbon::today());

        $user_activity = $user_activity->orderBy('id', 'desc')->paginate(10);
        if (count($user_activity)) {
            foreach ($user_activity as $key => $value) {
                if ($value->task_progress_percentage <= 50)
                    $value->delay_percentage_color = "success";
                if ($value->task_progress_percentage > 50)
                    $value->delay_percentage_color = "warning";
                if ($value->task_progress_percentage > 80)
                    $value->delay_percentage_color = "error";
                $value->time = Carbon::parse($value->created_at)->format('d/m/Y h:i A');
            }
        }
        return response()->json([
            'status' => "success",
            'result' => $user_activity
        ], 200);
    }
    public function attendance_log(Request $request)
    {
        $temp_data_1 = [];
        $temp_data_2 = [];
        $user_uuid = [];
        $attendance = Attendance::query();
        if ($request->search != 'null') {
            $employee_uuid = User::where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('position', 'like', '%' . $request->search . '%')
                ->orWhere('employee_id', 'like', '%' . $request->search . '%')->pluck('uuid')->toArray();
            if (count($employee_uuid) > 0)
                $attendance = $attendance->whereIn('user_uuid', $employee_uuid);
        }
        if ($request->date != 'null')
            $attendance = $attendance->whereDate('created_at', $request->date);
        if ($request->date == 'null' && $request->search == 'null')
            $attendance = $attendance->whereDate('created_at', Carbon::today());
        $attendance = $attendance->with('getEmployee')->get();
        if (count($attendance)) {
            $user_uuid = array_unique(array_column(json_decode($attendance), 'user_uuid'));
            foreach ($attendance as $key => $value) {
                $temp_data_1[$key]['status'] = $value->status;
                $temp_data_1[$key]['time'] = Carbon::parse($value->created_at)->format('d/m/Y h:i A');
                $temp_data_1[$key]['name'] = $value->getEmployee->name;
                $temp_data_1[$key]['employee_id'] = $value->getEmployee->employee_id;
                $temp_data_1[$key]['email'] = $value->getEmployee->email;
                $temp_data_1[$key]['position'] = $value->getEmployee->position;
                $temp_data_1[$key]['team_name'] = $value->getEmployee?->getTeam?->name;
            }
        }
        // return array_unique(array_column(json_decode($attendance), 'user_uuid'));
        // $user_uuid = Attendance::query();
        // if ($request->date != 'null')
        //     $user_uuid = $user_uuid->whereDate('created_at', $request->date);
        // if ($request->date == 'null' && $request->search == 'null')
        //     $user_uuid = $user_uuid->whereDate('created_at', Carbon::today());
        // $user_uuid = $user_uuid->pluck('user_uuid')->unique()->toArray();
        $employee = User::query();
        if ($request->search != 'null') {
            if (count($employee_uuid) > 0)
                $employee = $employee->whereIn('uuid', $employee_uuid);
        }
        $employee = $employee->whereNotIn('uuid', $user_uuid)->where('id', '!=', 1)->with('getTeam')->get();
        if (count($employee)) {
            foreach ($employee as $key => $value) {
                $temp_data_2[$key]['status'] = '0';
                $temp_data_2[$key]['time'] = Carbon::today()->format('d/m/Y');
                $temp_data_2[$key]['name'] = $value->name;
                $temp_data_2[$key]['employee_id'] = $value->employee_id;
                $temp_data_2[$key]['email'] = $value->email;
                $temp_data_2[$key]['position'] = $value->position;
                $temp_data_2[$key]['team_name'] = $value->getTeam?->name;
            }
        }
        return response()->json([
            'status' => "success",
            'result' => array_merge($temp_data_1, $temp_data_2)
        ], 200);
    }
}
