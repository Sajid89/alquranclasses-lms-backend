<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware {
    public function handle($request, Closure $next) {
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        $origin = $request->header('Origin');
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, baggage, sentry-trace');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
