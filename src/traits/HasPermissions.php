<?php

namespace Zaichaopan\AccessGranted\Traits;

use Zaichaopan\AccessGranted\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasPermissions
{
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions->contains('name', $permission);
    }

    public function givePermissionTo(string ...$permissionNames): bool
    {
        $permissions = Permission::whereIn('name', $permissionNames)->get();

        if ($permissions->count() === 0) {
            return false;
        }

        $this->permissions()->syncWithoutDetaching($permissions);

        return true;
    }

    public function updatePermissionTo(string ...$permissionNames): bool
    {
        $permissions = Permission::whereIn('name', $permissionNames)->get();

        if ($permissions->count() === 0) {
            return false;
        }

        $this->permissions()->sync($permissions);

        return true;
    }

    public function withdrawPermissionTo(string ...$permissionNames): bool
    {
        $permissions = Permission::whereIn('name', $permissionNames)->get();

        if ($permissions->count() === 0) {
            return false;
        }

        $this->permissions()->detach($permissions);

        return true;
    }

    public function withdrawAllPermissions(): self
    {
        $this->permissions()->detach();

        return $this;
    }
}
