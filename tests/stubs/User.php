<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Zaichaopan\AccessGranted\Traits\{HasPermissions, HasRolePermissions, HasRoles};

class User extends Model implements Authenticatable
{
    use AuthenticableTrait,
        HasRoles,
        HasRolePermissions,
        HasPermissions { hasPermission as hasPermissionThroughPermissionTrait; }

    protected $connection = 'testbench';

    protected $table = 'users';

    public function hasPermission(string $permission)
    {
        return $this->hasPermissionThroughPermissionTrait($permission) || $this->hasPermissionThroughRole($permission);
    }
}
