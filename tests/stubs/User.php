<?php

use Illuminate\Database\Eloquent\Model;
use Zaichaopan\Permission\Traits\{HasPermissions, HasRolePermissions, HasRoles};

class User extends Model
{
    use HasRoles,
        HasRolePermissions,
        HasPermissions { hasPermission as hasPermissionThroughPermissionTrait; }

    protected $connection = 'testbench';

    protected $table = 'users';

    public function hasPermission(string $permission)
    {
        return $this->hasPermissionThroughPermissionTrait($permission) || $this->hasPermissionThroughRole($permission);
    }
}
