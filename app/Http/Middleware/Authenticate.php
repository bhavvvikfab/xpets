<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : null;
    }

        /**
     * Handle unauthenticated API requests.
     */
    protected function unauthenticated($request, array $guards)
    {
        if (auth()->check()) {
            return response()->json(['message' => 'Authenticated user detected'], 200);
        }
        // Return a JSON response for unauthenticated API requests
        abort(response()->json(['error' => 'Unauthenticated'], 401));
    }
}
