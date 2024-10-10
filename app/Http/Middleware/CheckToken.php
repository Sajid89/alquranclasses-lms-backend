<?php

namespace App\Http\Middleware;
use Carbon\Carbon;
use Closure;

class CheckToken
{
    public function handle($request, Closure $next)
    {
        // Make sure the request is authenticated
        if (!auth()->check()) {
            return app('App\Http\Controllers\Controller')->error('Unauthenticated', 401);
        }

        // Get the token instance for the authenticated user
        $token = auth()->user()->token();

        // Check token expiration
        if ($token->expires_at < Carbon::now()) {
            return app('App\Http\Controllers\Controller')->error('Unauthenticated', 401);
        }

        return $next($request);
    }
}