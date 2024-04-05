<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\UserTaskActivity;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function check_login(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            if ($user == null)
                return response()->json([
                    'status' => "error",
                    'message' => "Incorrect email address."
                ], 404);

            if ($user->status == 0)
                return response()->json([
                    'status' => "error",
                    'message' => "Sorry your account is deactivated now."
                ], 404);

            if ($user && Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => "success",
                    'message' => "Login successfully",
                    'user_name' => $user->name,
                    'role_name' => $user->roles[0]->display_name,
                    'token' => $user->createToken('API Token')->accessToken,
                ], 200);
            } else {
                return response()->json([
                    'status' => "error",
                    'message' => "Incorrect password."
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => "error",
                'message' => $th->getMessage()
            ], 404);
        }
    }
    public function user_details()
    {
        $user = User::where('id', Auth::user()->id)->with(['getAttendance' => function ($query) {
            $query->whereDate('created_at', Carbon::today());
        }, 'roles' => function ($query1) {
            $query1->with('permissions');
        }])->first();
        return response()->json([
            'status' => "success",
            'result' => $user
        ], 200);
    }
    public function logout(Request $request, TaskController $taskController)
    {
        try {
            $get_today_task = Task::where('user_uuid', Auth::user()->uuid)->whereDate('created_at', Carbon::today())->get();
            if (count($get_today_task)) {
                foreach ($get_today_task as $value) {
                    if ($value->status == '1') {
                        $user_task_activity = new UserTaskActivity();
                        $user_task_activity->uuid = Str::uuid()->toString();
                        $user_task_activity->user_uuid = Auth::user()->uuid;
                        $user_task_activity->task_uuid = $value->uuid;
                        $user_task_activity->status = '2';
                        $user_task_activity->ip_address = $request->ip();
                        $user_task_activity->save();
                        if ($user_task_activity->save()) {
                            $all_task_activity = UserTaskActivity::where('task_uuid', $value->uuid)->where('user_uuid', Auth::user()->uuid)->orderBy('id', 'desc')->get();

                            if (count($all_task_activity) > 1) {
                                if (isset($all_task_activity[2]))
                                    $temp_val = $all_task_activity[2]->task_progress_percentage;
                                else
                                    $temp_val = 0;

                                $task_progress_percentage = $taskController->calculate_task_progress($all_task_activity[1]->created_at, $value->estimation_hrs, $temp_val);
                                $user_task_activity->task_progress_percentage = $task_progress_percentage;
                                $user_task_activity->save();
                            }
                            $value->status = '2';
                            $value->save();
                        }
                    }
                }
            }
            Auth::user()->token()->revoke();
            return response()->json([
                'status' => "success",
                'message' => "Logout successfully"
            ], 200);
        } catch (\Throwable $e) {
            Log::info('logout :-' . $e->getMessage());
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage(),
            ], 404);
        }
    }
    public function all_team()
    {
        return response()->json([
            'status' => "success",
            'result' => Team::get()
        ], 200);
    }
    public function all_employee(Request $request)
    {
        $user = User::query();
        if ($request->search != 'null') {
            $user = $user->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('position', 'like', '%' . $request->search . '%')
                ->orWhere('employee_id', 'like', '%' . $request->search . '%');
        } else {
            $user = $user->with(['getTeam', 'getRole', 'getAttendance']);
        }
        $user = $user->paginate(10);
        if (count($user)) {
            $count = $user->perPage() * ($user->currentPage() - 1) + 1;
            foreach ($user as $key => $value) {
                $value->count = $count;
                $count++;
            }
        }
        return response()->json([
            'status' => "success",
            'result' => $user
        ], 200);
    }
    public function employee_create(Request $request)
    {
        try {
            $is_customer_exist = User::where('email', $request->email)->first();
            if ($is_customer_exist != null)
                return response()->json([
                    'status' => "error",
                    'message' => "Email already exist."
                ], 404);
            $check_employee_id = User::where('employee_id', $request->employee_id)->first();
            if ($check_employee_id != null)
                return response()->json([
                    'status' => "error",
                    'message' => "Employee ID already exist."
                ], 404);
            $user = new User();
            $user->uuid = Str::uuid()->toString();
            $user->name = $request->full_name;
            $user->employee_id = $request->employee_id;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->position = $request->position;
            $user->team_uuid = $request->team_uuid;
            $user->status = 1;
            $user->save();
            $user->addRole($request->role);
            return response()->json([
                'status' => "success",
                'message' => "Employee register successfully.",
            ], 200);
        } catch (Exception $e) {
            Log::info('Error in employee registration :-' . $e->getMessage());
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage(),
            ], 404);
        }
    }
    public function employee_delete(Request $request)
    {
        try {
            $is_customer_exist = User::where('uuid', $request->uuid)->first();
            if ($is_customer_exist == null)
                return response()->json([
                    'status' => "error",
                    'message' => "Employee does not exist!"
                ], 404);

            $is_customer_exist->delete();
            return response()->json([
                'status' => "success",
                'message' => "Employee deleted successfully.",
            ], 200);
        } catch (Exception $e) {
            Log::info('Error in employee delete :-' . $e->getMessage());
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage(),
            ], 404);
        }
    }
    public function employee_update(Request $request)
    {
        try {
            $user = User::where('uuid', $request->uuid)->first();
            if ($user == null)
                return response()->json([
                    'status' => "error",
                    'message' => "Employee does not exist!"
                ], 404);
            if ($request->password != null)
                $user->password = Hash::make($request->password);
            $user->status = $request->status;
            $user->position = $request->position;
            $user->save();
            $user->roles()->sync($request->role);
            return response()->json([
                'status' => "success",
                'message' => "Employee updated successfully.",
            ], 200);
        } catch (Exception $e) {
            Log::info('Error in employee update :-' . $e->getMessage());
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage(),
            ], 404);
        }
    }
    public function change_availability(Request $request)
    {
        try {
            $user = User::where('uuid', Auth::user()->uuid)->first();
            if ($user == null)
                return response()->json([
                    'status' => "error",
                    'message' => "Employee does not exist!"
                ], 404);
            if ($user->availability != (int)$request->availability) {
                $user->availability = (int)$request->availability;
                $user->save();
                return response()->json([
                    'status' => "success",
                    'message' => "Employee availability updated successfully.",
                ], 200);
            }
        } catch (Exception $e) {
            Log::info('Error in employee availability updated successfully :-' . $e->getMessage());
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage(),
            ], 404);
        }
    }
    public function change_attendance(Request $request)
    {
        try {
            $check_attendance = Attendance::where('user_uuid', Auth::user()->uuid)->whereDate('created_at', Carbon::today())->orderBy('id', 'desc')->first();
            if ($check_attendance == null) {
                $get_latest_record_date = Task::where('user_uuid', Auth::user()->uuid)->orderBy('created_at', 'desc')->first('created_at');
                if ($get_latest_record_date != null) {
                    $previous_task = Task::where('user_uuid', Auth::user()->uuid)->whereIn('status', [0, 2])->whereDate('created_at', $get_latest_record_date->created_at)->get();
                    if (count($previous_task)) {
                        foreach ($previous_task as $value) {
                            $task = new Task();
                            $task->uuid = Str::uuid()->toString();
                            $task->user_uuid = Auth::user()->uuid;
                            $task->task_name = $value->task_name;
                            $task->project_name = $value->project_name;
                            $task->estimation_hrs = $value->estimation_hrs;
                            $task->completion_hrs = 0;
                            $task->status = $value->status;
                            $task->type = '1';
                            $task->priority = $value->priority;
                            $task->save();
                            if ($task->save()) {
                                $get_user_task_activity = UserTaskActivity::where('task_uuid', $value->uuid)->where('user_uuid', Auth::user()->uuid)->orderBy('id', 'desc')->first();
                                if ($get_user_task_activity != null) {
                                    $user_task_activity = new UserTaskActivity();
                                    $user_task_activity->uuid = Str::uuid()->toString();
                                    $user_task_activity->user_uuid = Auth::user()->uuid;
                                    $user_task_activity->task_uuid = $task->uuid;
                                    $user_task_activity->status = $get_user_task_activity->status;
                                    $user_task_activity->task_progress_percentage = $get_user_task_activity->task_progress_percentage;
                                    $user_task_activity->ip_address = $request->ip();
                                    $user_task_activity->save();
                                }
                            }
                        }
                    }
                }
            }
            
            if (!$check_attendance || Carbon::parse($check_attendance->created_at)->addHour()->lte(now())) {
                $attendance = new Attendance();
                $attendance->uuid = Str::uuid()->toString();
                $attendance->user_uuid = Auth::user()->uuid;
                $attendance->status = $request->status;
                $attendance->ip_address = $request->ip();
                $attendance->save();
                return response()->json([
                    'status' => "success",
                    'message' => "Employee attendance  updated successfully.",
                ], 200);
            } else {
                return response()->json([
                    'status' => "warning",
                    'message' => "After one hour you can change you attendance.",
                ], 200);
            }
        } catch (Exception $e) {
            Log::info('Error in employee attendance updated successfully :-' . $e->getMessage());
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
