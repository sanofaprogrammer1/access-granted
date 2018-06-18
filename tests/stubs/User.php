<?php

use Illuminate\Database\Eloquent\Model;
use Zaichaopan\Permission\Traits\{HasPermissionsTrait, HasRolePermissionsTrait, HasRolesTrait};

class User extends Model
{
    use HasRolesTrait,
        HasRolePermissionsTrait,
        HasPermissionsTrait { hasPermission as hasPermissionThroughPermissionTrait; }

    protected $connection = 'testbench';

    protected $table = 'users';

    public function hasPermission(string $permission)
    {
        return $this->hasPermissionThroughPermissionTrait($permission) || $this->hasPermissionThroughRole($permission);
    }
}
