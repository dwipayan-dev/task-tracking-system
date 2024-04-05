<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attendance extends Model
{
    use HasFactory;
    public $fillable=[
        'uuid',
        'user_uuid',
        'status',
        'ip_address'
    ];
    public function getEmployee(): HasOne
    {
        return $this->hasOne('App\Models\User', 'uuid', 'user_uuid')->with('getTeam');
    }
}
