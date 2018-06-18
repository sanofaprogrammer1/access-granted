<?php

namespace Zaichaopan\Permission\Traits;

trait HasRolePermissionsTrait
{
    public function hasPermissionThroughRole(string $permission) : bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
