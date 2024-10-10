<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    protected $rateLimiter;

    public function __construct(RateLimiter $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

    public function handle($request, Closure $next)
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = 500;
        $decayMinutes = 1;

        if ($this->rateLimiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts, $decayMinutes);
        }

        $this->rateLimiter->hit($key, $decayMinutes * 60);

        $response = $next($request);
        return $this->addHeaders(
            $response, $maxAttempts,
            $this->rateLimiter->remaining($key, $maxAttempts),
            $this->rateLimiter->availableIn($key),
            $decayMinutes
        );
    }

    protected function resolveRequestSignature($request)
    {
        return sha1($request->ip());
    }

    protected function buildResponse($key, $maxAttempts, $decayMinutes)
    {
        $retryAfter = $this->rateLimiter->availableIn($key);
        $response = response()->json([
            'success' => false,
            'message' => 'Too Many Requests.'
        ], 429);

        return $this->addHeaders($response, $maxAttempts, 0, $retryAfter, $decayMinutes);
    }

    protected function addHeaders(Response $response, $maxAttempts, $remainingAttempts, $retryAfter, $decayMinutes)
    {
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remainingAttempts);
        $response->headers->set('Retry-After', $retryAfter);
        $response->headers->set('X-RateLimit-Reset', time() + ($decayMinutes * 60));

        return $response;
    }
}
