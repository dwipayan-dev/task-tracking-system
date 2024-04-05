<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RoleUser extends Model
{
    use HasFactory;
    public $table = 'role_user';

    public function getRoleDetails():HasOne {
        return $this->hasOne(Role::class, 'id', 'role_id')->with('permissions');
    }
}
