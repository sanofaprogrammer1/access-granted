<?php

namespace Zaichaopan\Permission\Traits;

use Zaichaopan\Permission\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    public function roles() : BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function giveRole(string ...$roleNames) : bool
    {
        $roles = Role::whereIn('name', $roleNames)->get();

        if ($roles->count() === 0) {
            return false;
        }

        $this->roles()->syncWithoutDetaching($roles);

        return true;
    }

    public function removeRole(string ...$roleNames) : self
    {
        $roles = Role::whereIn('name', $roleNames)->get();

        if ($roles->count() === 0) {
            return $this;
        }

        $this->roles()->detach($roles);

        return $this;
    }

    public function removeAllRoles() : self
    {
        $this->roles()->detach();

        return $this;
    }

    public function updateRole(string ...$roleNames) : self
    {
        $roles = Role::whereIn('name', $roleNames)->get();

        if ($roles->count() === 0) {
            return $this;
        }

        $this->roles()->sync($roles);

        return $this;
    }

    public function hasRole(string ...$roles) : bool
    {
        foreach ($roles as $role) {
            if ($this->roles()->whereName($role)->exists()) {
                return true;
            }
        }

        return false;
    }
}
