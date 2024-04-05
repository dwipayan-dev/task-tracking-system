<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'employee_id',
        'email',
        'password',
        'position',
        'team_uuid',
        'status',
        'availability'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function getTeam(): HasOne
    {
        return $this->hasOne('App\Models\Team', 'uuid', 'team_uuid');
    }
    public function getRole(): HasOne
    {
        return $this->hasOne(RoleUser::class, 'user_id', 'id')->with('getRoleDetails');
    }
    public function getAttendance(): HasOne
    {
        return $this->hasOne(Attendance::class, 'user_uuid', 'uuid')->orderBy('id','desc');
    }
}
