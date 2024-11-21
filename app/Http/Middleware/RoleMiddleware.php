<?php

// app/Http/Middleware/RoleMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Check if the user is authenticated and has the required role
        if (!Auth::check() || !Auth::user()->hasRole($role)) {
            return response()->json(['error' => 'Unauthorized'], 403); // Forbidden
        }

        return $next($request);
    }
}
