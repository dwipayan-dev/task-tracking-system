<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Laratrust\Models\Role as RoleModel;

class Role extends RoleModel
{
    public $guarded = [];
    public function getPermissions(): HasMany
    {
        return $this->hasMany(PermissionRole::class, 'role_id', 'id')->with('getPermissionsName');
    }
}
