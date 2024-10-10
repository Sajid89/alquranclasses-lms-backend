<?php

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PassportService
{
    /**
     * Create access, refresh tokens
     *
     * @param array $data
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    
    public function handleOAuthRequest($data, $message)
    {
        $tokenRequest = Request::create('/oauth/token', 'POST', $data);
        $response = app()->handle($tokenRequest);
    
        if ($response->getStatusCode() != 200) {
            return response()->json(json_decode($response->getContent()), $response->getStatusCode());
        }
    
        $tokenData = json_decode($response->getContent());
    
        // Set token expiration
        $accessTokenExpiry = $tokenData->expires_in / 60; // in minutes
        $refreshTokenExpiry = 14 * 24 * 60; // 14 days
    
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'access_token' => $tokenData->access_token,
                'expires_in'   => $tokenData->expires_in,
            ]
        ], 200);
    }

    /**
     * Create access token and set it in Http cookie
     *
     * @param $user
     * @param $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAccessTokenAndCookie($user, $message)
    {
        $tokenResult = $user->createToken('accessToken');

        $accessTokenExpiry = $tokenResult->token->expires_at->diffInMinutes(Carbon::now());

        // Create HttpOnly cookie
        $accessCookie = new Cookie(
            'accessToken',
            $tokenResult->accessToken,
            time() + (60 * $accessTokenExpiry),
            '/',
            'localhost', // Set the Domain attribute to localhost
            true,
            true,
            false,
            'None'
        );

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => []
        ], 200)
        ->withCookie($accessCookie);
    }

    /**
     * Remove access, refresh tokens from Http cookies
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCookies($message)
    {
        // Create expired cookies
        $clearAccessCookie = new Cookie(
            'accessToken',
            null,
            time() - 4200,
            '/',
            'localhost', // Set the Domain attribute to localhost
            true,
            true,
            false,
            'None'
        );

        $clearRefreshCookie = new Cookie(
            'refreshToken',
            null,
            time() - 4200,
            '/',
            'localhost', // Set the Domain attribute to localhost
            true,
            true,
            false,
            'None'
        );

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => []
        ], 200)
        ->withCookie($clearAccessCookie)
        ->withCookie($clearRefreshCookie);
    }
}