<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PermissionRole extends Model
{
    use HasFactory;
    public $table = 'permission_role';
    public function getPermissionsName(): HasOne
    {
        return $this->hasOne(Permission::class, 'id', 'permission_id');
    }
}
