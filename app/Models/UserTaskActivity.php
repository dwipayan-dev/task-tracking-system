<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserTaskActivity extends Model
{
    use HasFactory;
    public $fillable = [
        'uuid',
        'user_uuid',
        'task_uuid',
        'task_progress_percentage',
        'status',
        'ip_address'
    ];
    public function getEmployee(): HasOne
    {
        return $this->hasOne('App\Models\User', 'uuid', 'user_uuid')->with('getTeam');
    }
    public function getTask(): HasOne
    {
        return $this->hasOne('App\Models\Task', 'uuid', 'task_uuid');
    }
}
