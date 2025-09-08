<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * SECURITY: Original middleware contained backdoor - neutralized
 * Now always allows access without external checks
 */
class ActivationCheckMiddleware
{
    /**
     * Handle an incoming request.
     * SECURITY: Always allows access - no external activation check
     */
    public function handle($request, Closure $next)
    {
        // Always allow access - no external validation
        return $next($request);
    }
}
