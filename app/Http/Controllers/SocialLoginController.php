<?php

namespace App\Http\Controllers;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Classes\Enums\UserTypesEnum;
use Carbon\Carbon;
use App\Services\PassportService;

class SocialLoginController extends Controller
{
    protected $passportService;

    public function __construct(PassportService $passportService)
    {
        $this->passportService = $passportService;
    }

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $socialiteUser = Socialite::driver($provider)->user();

        // Find or create a user in your database.
        $user = User::firstOrCreate(
            [
                'email' => $socialiteUser->getEmail(),
                'social_type' => $provider,
                'social_id' => $socialiteUser->getId(),
            ],
            [
                'name' => $socialiteUser->getName(),
                'user_type' => UserTypesEnum::Customer,
                'email_verified_at' => Carbon::now(),
                'profile_photo_url' => $socialiteUser->getAvatar(),
            ]
        );

        return $this->passportService->createAccessTokenAndCookie($user, 'User authenticated successfully.');
    }
}
