<?php

namespace Zaichaopan\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Zaichaopan\Permission\Traits\HasPermissionsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasPermissionsTrait;

    protected $guarded = [];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
