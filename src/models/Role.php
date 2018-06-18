<?php

namespace Zaichaopan\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Zaichaopan\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasPermissions;

    protected $guarded = [];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
