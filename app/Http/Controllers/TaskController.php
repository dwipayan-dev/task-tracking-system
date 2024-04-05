<?php

namespace App\Http\Controllers;

use App\Exports\AllTaskExport;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTaskActivity;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Excel;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $task = Task::query();
        $task = $task->where('user_uuid', Auth::user()->uuid)->with('getTaskActivity');

        if ($request->search != 'null') {
            $search = $request->search;
            $task = $task->where(function ($query) use ($search) {
                $query->where('task_name', 'like', '%' .  $search . '%')
                    ->orWhere('project_name', 'like', '%' .  $search . '%');
            });
        }
        if ($request->date != 'null') {
            $task = $task->whereDate('created_at', $request->date);
        }
        if ($request->status != 'null') {
            $task = $task->where('status', (string) $request->status);
        }
        if ($request->priority != 'null') {
            $task = $task->where('priority', (string) $request->priority);
        }
        if ($request->type != 'null') {
            $task = $task->where('type', (string) $request->type);
        }
        if ($request->date == 'null' && $request->search == 'null' && $request->status == 'null' && $request->priority == 'null' && $request->type == 'null')
            $task = $task->whereDate('created_at', Carbon::today());

        $task = $task->orderBy('id', 'desc')->paginate(15);

        if (count($task)) {
            $count = $task->perPage() * ($task->currentPage() - 1) + 1;
            foreach ($task as $key => $value) {
                if (isset($value->getTaskActivity)) {
                    if (count($value->getTaskActivity)) {
                        if ($value->status == 0) {
                            $value->delay_percentage = 0;
                        } elseif ($value->getTaskActivity[0]['status'] == 1) {
                            if (isset($value->getTaskActivity[1]))
                                $temp_val = $value->getTaskActivity[1]->task_progress_percentage;
                            else
                                $temp_val = 0;
                            $task_progress_percentage = $this->calculate_task_progress($value->getTaskActivity[0]->created_at, $value->estimation_hrs, $temp_val);
                            $value->delay_percentage = $task_progress_percentage;
                        } else {
                            $value->delay_percentage = $value->getTaskActivity[0]['task_progress_percentage'];
                        }
                    } else {
                        $value->delay_percentage = 0;
                    }
                } else {
                    $value->delay_percentage = 0;
                }

                if ($value->delay_percentage <= 50)
                    $value->delay_percentage_color = "success";
                if ($value->delay_percentage > 50)
                    $value->delay_percentage_color = "warning";
                if ($value->delay_percentage >= 80)
                    $value->delay_percentage_color = "error";

                $value->created_time = Carbon::parse($value->created_at)->format('d/m/Y h:i A');
                $value->count = $count;
                $count++;
            }
        }
        return response()->json([
            'status' => "success",
            'result' => $task
        ], 200);
    }
    public function all_task(Request $request)
    {
        $task = Task::query();
        $task = $task->with('getEmployee');
        if ($request->search != 'null') {
            $employee_uuid = User::where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('position', 'like', '%' . $request->search . '%')
                ->orWhere('employee_id', 'like', '%' . $request->search . '%')->pluck('uuid')->toArray();

            if (count($employee_uuid) > 0)
                $task = $task->whereIn('user_uuid', $employee_uuid);
            else {
                $task = $task->where('task_name', 'like', '%' . $request->search . '%')
                    ->orWhere('project_name', 'like', '%' . $request->search . '%');
            }
        }
        if ($request->date != 'null') {
            $task = $task->whereDate('created_at', $request->date);
        }
        if ($request->status != 'null') {
            $task = $task->where('status', (string) $request->status);
        }
        if ($request->priority != 'null') {
            $task = $task->where('priority', (string) $request->priority);
        }
        if ($request->type != 'null') {
            $task = $task->where('type', (string) $request->type);
        }
        if ($request->date == 'null' && $request->search == 'null' && $request->status == 'null' && $request->priority == 'null' && $request->type == 'null')
            $task = $task->whereDate('created_at', Carbon::today());
        $task = $task->orderBy('id', 'desc')->paginate(15);

        if (count($task)) {
            $count = $task->perPage() * ($task->currentPage() - 1) + 1;
            foreach ($task as $key => $value) {
                if (isset($value->getTaskActivity)) {
                    if (count($value->getTaskActivity)) {
                        if ($value->status == 0) {
                            $value->delay_percentage = 0;
                        } elseif ($value->getTaskActivity[0]['status'] == 1) {
                            if (isset($value->getTaskActivity[1]))
                                $temp_val = $value->getTaskActivity[1]->task_progress_percentage;
                            else
                                $temp_val = 0;
                            $task_progress_percentage = $this->calculate_task_progress($value->getTaskActivity[0]->created_at, $value->estimation_hrs, $temp_val);
                            $value->delay_percentage = $task_progress_percentage;
                        } else {
                            $value->delay_percentage = $value->getTaskActivity[0]['task_progress_percentage'];
                        }
                    } else {
                        $value->delay_percentage = 0;
                    }
                } else {
                    $value->delay_percentage = 0;
                }

                if ($value->delay_percentage <= 50)
                    $value->delay_percentage_color = "success";
                if ($value->delay_percentage > 50)
                    $value->delay_percentage_color = "warning";
                if ($value->delay_percentage >= 80)
                    $value->delay_percentage_color = "error";

                $value->created_time = Carbon::parse($value->created_at)->format('d/m/Y h:i A');
                $value->count = $count;
                $count++;
            }
        }
        return response()->json([
            'status' => "success",
            'result' => $task
        ], 200);
    }
    public function create(Request $request)
    {
        try {
            $check_attendance = User::where('uuid', Auth::user()->uuid)->with(['getAttendance' => function ($query) {
                $query->whereDate('created_at', Carbon::today());
            }])->first();
            if ($check_attendance->getAttendance == null)
                return response()->json([
                    'status' => "error",
                    'message' => 'Please complete your attendance',
                ], 404);
            $task = new Task();
            $task->uuid = Str::uuid()->toString();
            $task->user_uuid = Auth::user()->uuid;
            $task->task_name = $request->task_name;
            $task->project_name = $request->project_name;
            $task->estimation_hrs = $request->estimation_hr;
            $task->completion_hrs = 0;
            $task->status = 0;
            $task->type = '0';
            $task->priority = (string)$request->priority;
            $task->save();
            return response()->json([
                'status' => "success",
                'message' => "Task created successfully.",
            ], 200);
        } catch (Exception $e) {
            Log::info('Error in task create :-' . $e->getMessage());
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage(),
            ], 404);
        }
    }
    public function update_task_status(Request $request)
    {
        try {
            $user_task_activity = new UserTaskActivity();
            $user_task_activity->uuid = Str::uuid()->toString();
            $user_task_activity->user_uuid = Auth::user()->uuid;
            $user_task_activity->task_uuid = $request->task_uuid;
            $user_task_activity->status = $request->status;
            $user_task_activity->ip_address = $request->ip();
            $user_task_activity->save();
            if ($user_task_activity->save()) {
                $task = Task::where('uuid', $request->task_uuid)->first();
                $all_task_activity = UserTaskActivity::where('task_uuid', $request->task_uuid)->where('user_uuid', Auth::user()->uuid)->orderBy('id', 'desc')->get();

                if (count($all_task_activity) > 1) {
                    if ($request->status == 2 || $request->status == 3) {
                        if (isset($all_task_activity[2]))
                            $temp_val = $all_task_activity[2]->task_progress_percentage;
                        else
                            $temp_val = 0;

                        $task_progress_percentage = $this->calculate_task_progress($all_task_activity[1]->created_at, $task->estimation_hrs, $temp_val);
                        $user_task_activity->task_progress_percentage = $task_progress_percentage;
                        $user_task_activity->save();
                    }
                }
                if ($task == null) {
                    return response()->json([
                        'status' => "error",
                        'message' => "Task not found.",
                        'result' => 0
                    ], 404);
                }
                if ($request->status == 3) {
                    if ($task->type == '1') {
                        $min = $task->estimation_hrs * 60;
                        $convert_hrs = $min / 100;
                        $progress_min = $convert_hrs * $user_task_activity->task_progress_percentage;
                        $progress_hrs = $progress_min / 60;
                        $task->completion_hrs = $progress_hrs;
                    } else {
                        $all_task_activity = UserTaskActivity::where('task_uuid', $request->task_uuid)->where('user_uuid', Auth::user()->uuid)->orderBy('id', 'asc')->get();
                        if (count($all_task_activity)) {
                            $all_task_activity = json_decode($all_task_activity, true);
                            $pairs = array_chunk($all_task_activity, 2);
                            $calculate_estimation = 0;
                            foreach ($pairs as $pair) {
                                $status1 = $pair[0]['status'];
                                $status2 = isset($pair[1]) ? $pair[1]['status'] : null;
                                if ($status2 != null) {
                                    if ($status1 != 2 && $status2 != 1) {
                                        $startDate = new Carbon($pair[0]['created_at']);
                                        $endDate = new Carbon($pair[1]['created_at']);
                                        $calculate_estimation += $endDate->diffInMinutes($startDate);
                                    }
                                }
                            }
                            if ($calculate_estimation > 0)
                                $task->completion_hrs = $calculate_estimation / 60;
                        }
                    }
                }
                $task->status = $request->status;
                $task->save();
            }
            return response()->json([
                'status' => "success",
                'message' => "Status updated successfully.",
                'result' => 0
            ], 200);
        } catch (Exception $e) {
            Log::info('Error in task status update :-' . $e->getMessage());
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function calculate_task_progress($start_time, $estimation_hrs, $previous_progress)
    {
        $startTime = Carbon::parse($start_time);
        $currentTime = Carbon::now();
        $elapsedTimeInMinitue = $currentTime->diffInMinutes($startTime);
        $elapsedTimeInHours = $elapsedTimeInMinitue / 60;
        $completionPercentage = ($elapsedTimeInHours / $estimation_hrs) * 100;
        $task_progress_percentage = $completionPercentage + $previous_progress;
        return $task_progress_percentage;
    }

    public function all_task_excel_export(Request $request)
    {
        return Excel::download(new AllTaskExport($request->search, $request->date, $request->status, $request->priority, $request->type), 'all-task.xlsx');
    }
}
