<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ... $roles)
    {

        if (in_array(Auth::user()->role, $roles)) {
            return $next($request);
        }

        if (Auth::user()->role === 'admin') {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}
