<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Http;

class CountryRestrictionMiddleware
{
    protected $allowedCountries = ['US', 'CA', 'AU', 'GB', 'NL', 'DE', 'PK'];

    public function handle($request, Closure $next)
    {
        $ip = $request->ip();

        if ($ip === '::1' || $ip === '127.0.0.1') {
            return $next($request);
        }

        $response = Http::get("http://ipinfo.io/{$ip}/json");

        $locationData = $response->json();
        $countryCode = $locationData['country'] ?? null;

        // Check if the country is in the list of restricted countries
        if (!in_array($countryCode, $this->allowedCountries)) {
            return app('App\Http\Controllers\Controller')->error('Unfortunately We are not providing services in your country', 403);
        }

        return $next($request);
    }
}
