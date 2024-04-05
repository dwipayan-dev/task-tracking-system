<?php

namespace App\Exports;

use App\Http\Controllers\TaskController;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class AllTaskExport implements FromCollection, WithHeadings, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public $search;
    public $date;
    public $status;
    public $priority;
    public $type;

    public function __construct($search, $date, $status, $priority, $type)
    {
        $this->search = $search;
        $this->date = $date;
        $this->status = $status;
        $this->priority = $priority;
        $this->type = $type;
    }
    public function collection()
    {
        $task = Task::query();
        $task = $task->with('getEmployee');
        if ($this->search != 'null') {
            $employee_uuid = User::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->orWhere('position', 'like', '%' . $this->search . '%')
                ->orWhere('employee_id', 'like', '%' . $this->search . '%')->pluck('uuid')->toArray();

            if (count($employee_uuid) > 0)
                $task = $task->whereIn('user_uuid', $employee_uuid);
            else {
                $task = $task->where('task_name', 'like', '%' . $this->search . '%')
                    ->orWhere('project_name', 'like', '%' . $this->search . '%');
            }
        }
        if ($this->date != 'null') {
            $task = $task->whereDate('created_at', $this->date);
        }
        if ($this->status != 'null') {
            $task = $task->where('status', (string) $this->status);
        }
        if ($this->priority != 'null') {
            $task = $task->where('priority', (string) $this->priority);
        }
        if ($this->type != 'null') {
            $task = $task->where('type', (string) $this->type);
        }
        if ($this->date == 'null' && $this->search == 'null' && $this->status == 'null' && $this->priority == 'null' && $this->type == 'null')
            $task = $task->whereDate('created_at', Carbon::today());
        $task = $task->orderBy('id', 'desc')->get();

        if (count($task)) {
            foreach ($task as $key => $value) {
                $delay_percentage = 0;
                if (isset($value->getTaskActivity)) {
                    if (count($value->getTaskActivity)) {
                        if ($value->status == 0) {
                        } elseif ($value->getTaskActivity[0]['status'] == 1) {
                            if (isset($value->getTaskActivity[1]))
                                $temp_val = $value->getTaskActivity[1]->task_progress_percentage;
                            else
                                $temp_val = 0;

                            $taskController = new TaskController();
                            $task_progress_percentage = $taskController->calculate_task_progress($value->getTaskActivity[0]->created_at, $value->estimation_hrs, $temp_val);
                            $delay_percentage = $task_progress_percentage;
                        } else {
                            $delay_percentage = $value->getTaskActivity[0]['task_progress_percentage'];
                        }
                    } else {
                        $delay_percentage = 0;
                    }
                } else {
                    $delay_percentage = 0;
                }

                if ($value->type == '1')
                    $type = 'Carry Forward';
                else
                    $type = 'Newly Created';

                if ($value->priority == '0')
                    $priority = 'Low';
                elseif ($value->priority == '1')
                    $priority = 'Medium';
                else
                    $priority = 'High';

                $diff = $value->estimation_hrs - $value->completion_hrs;

                if ($diff < 0) {
                    $delay = round((abs($diff) * 100) / 100, 2);
                } else {
                    $delay = "No Delay";
                }

                if ($value->status == 0)
                    $status = 'Initiation';
                elseif ($value->status == 1)
                    $status = 'In Progress';
                elseif ($value->status == 2)
                    $status = 'Pause';
                else
                    $status = 'Completed';

                $created_time = Carbon::parse($value->created_at)->format('d/m/Y h:i A');

                $temp_data[] = [$value->getEmployee->employee_id, $value->getEmployee->name, $value->getEmployee->email, $value->getEmployee->position, $value->getEmployee->getTeam->name,  $value->task_name, $value->project_name, $type, $priority, $value->estimation_hrs, $delay_percentage, $value->completion_hrs, $delay, $status, $created_time];
            }
        }
        return collect($temp_data);
    }
    public function headings(): array
    {
        return ['ID', 'Name', 'Email', 'Position', 'Team', 'Task Name', 'Project Name', 'Task Type', 'Priority', 'Estimation(in hours)', 'Progress(in Percentage)', 'Completed(in hours)', 'Delay', 'Status', 'Created Time'];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:O1')->getFont()->setBold(true);
            },
        ];
    }
}
