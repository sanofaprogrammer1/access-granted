<?php

namespace Zaichaopan\Permission\Middlewares;

use Closure;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (!$user = $request->user()) {
            return abort(401);
        }

        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        return abort(403);
    }
}
