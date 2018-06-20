<?php

namespace Zaichaopan\AccessGranted\Models;

use Illuminate\Database\Eloquent\Model;
use Zaichaopan\AccessGranted\Traits\HasPermissions;
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
