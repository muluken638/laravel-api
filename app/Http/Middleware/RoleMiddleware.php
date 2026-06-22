<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */

       public function handle($request, Closure $next, $role)
{
    if (!auth()->check()) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    if (auth()->user()->role !== $role) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    return $next($request);
}
    
}
