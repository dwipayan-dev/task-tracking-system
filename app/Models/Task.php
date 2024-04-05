<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Task extends Model
{
    use HasFactory;
    public $fillable = [
        'uuid',
        'user_uuid',
        'task_name',
        'project_name',
        'estimation_hrs',
        'completion_hrs',
        'status',
        'type',
        'priority'
    ];
    public function getTaskActivity(): HasMany
    {
        return $this->hasMany('App\Models\UserTaskActivity', 'task_uuid', 'uuid')->orderBy('id', 'desc');
    }
    public function getEmployee(): HasOne
    {
        return $this->hasOne('App\Models\User', 'uuid', 'user_uuid')->with(['getTeam', 'getAttendance']);
    }
}
