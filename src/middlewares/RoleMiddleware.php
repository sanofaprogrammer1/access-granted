<?php

namespace Zaichaopan\AccessGranted\Middlewares;

use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (!$user = $request->user()) {
            return abort(401);
        }

        if ($user->hasRole($role)) {
            return $next($request);
        }

        return abort(403);
    }
}
