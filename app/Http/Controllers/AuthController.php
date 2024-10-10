<?php

namespace App\Http\Controllers;
use App\Classes\Enums\UserTypesEnum;
use App\Helpers\GeneralHelper;
use App\Http\Requests\AuthRequest;
use App\Jobs\SendPasswordChangedEmail;
use App\Jobs\SendPasswordResetEmail;
use App\Jobs\SendVerificationEmail;
use App\Jobs\SendResetParentalPinEmail;
use App\Jobs\SendWelcomeMailCustomer;
use App\Jobs\SendWelcomeMailSchedulingTeam;
use App\Models\DeviceToken;
use App\Models\ParentalPinToken;
use App\Models\PasswordReset;
use App\Repository\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\PassportService;
use App\Services\SessionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Client;

class AuthController extends Controller
{
    protected $authRequest;
    protected $passportService;
    protected $userRepository;
    protected $sessionService;

    public function __construct(
        AuthRequest $authRequest, 
        PassportService $passportService,
        UserRepository $userRepository,
        SessionService $sessionService
    )
    {
        $this->authRequest = $authRequest;
        $this->passportService = $passportService;
        $this->userRepository = $userRepository;
        $this->sessionService = $sessionService;
    }

    /**
     * Signup API to register new customer
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function signup(Request $request)
    {
        $this->authRequest->validate($request);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => app('hash')->make($request->password),
            'phone'     => $request->phone,
            'user_type' => UserTypesEnum::Customer,
            'country'   => $request->country,
            'timezone'  => $request->timezone,
        ];

        $verificationToken = Str::random(60);
        $data['verification_token'] = $verificationToken;
        // Set the verification token to expire in 24 hours
        $data['verification_token_expires_at'] = Carbon::now()->addHours(24);

        if ($request->hasFile('profile_photo_url')) {
            $file = $request->file('profile_photo_url');
            $path = 'images/customer/profile';
            $data['profile_photo_path'] = GeneralHelper::uploadProfileImage($file, $path);
        }

        $user = User::create($data);
        $verificationUrl = $this->generateVerificationUrl('email-verification', $user->verification_token);
        
        $emailData = [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'verification_link' => $verificationUrl
        ];

        dispatch(new SendVerificationEmail($emailData));

        return $this->success($user, 'Signup successfully', 201);
    }

    /**
     * Email verify
     *
     * @param $token
     * @return JsonResponse
     * @throws ValidationException
     */
    public function verify($token)
    {
        $this->authRequest->validateEmailVerify($token);

        $user = User::select('id', 'name', 'email', 'verification_token', 'verification_token_expires_at', 'email_verified_at')
            ->where('verification_token', $token)->first();

        if (!$user) {
            return $this->error('Invalid verification token.', 422);
        }

        if ($user->email_verified_at) {
            return $this->error('Email is already verified.', 422);
        }

        if (Carbon::now()->greaterThan($user->verification_token_expires_at)) {
            return $this->error('Verification token has expired.', 422);
        }

        $user->update([
            'email_verified_at' => Carbon::now(),
            'verification_token' => null,
            'verification_token_expires_at' => null
        ]);

        $emailData = [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'customer_country' => $user->country,
            'customer_timezone' => $user->timezone,
            'customer_register_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        dispatch(new SendWelcomeMailCustomer($emailData));
        dispatch(new SendWelcomeMailSchedulingTeam($emailData));

        return $this->success([], 'Email verified', 200);
    }

    /**
     * Resend verification email
     * 
     * @param $token
     * @return JsonResponse
     * @throws ValidationException
     */
    public function resendVerificationEmail(Request $request)
    {
        $this->authRequest->validateForgotPassword($request);
        $email = $request->input('email');

        $user = User::select('id', 'name', 'email', 'verification_token', 'verification_token_expires_at', 'email_verified_at')
            ->where('email', $email)->first();
    
        if (!$user) {
            return $this->error('User not found', 404);
        }
    
        if ($user->email_verified_at) {
            return $this->error('Email is already verified.', 422);
        }
        
        if (Carbon::now()->lessThan(new Carbon($user->verification_token_expires_at))) {
            return $this->error('Verification email has already been sent.', 422);
        }
    
        $verificationToken = Str::random(60);
        $user->update([
            'verification_token' => $verificationToken,
            'verification_token_expires_at' => Carbon::now()->addHours(24)
        ]);
    
        $verificationUrl = $this->generateVerificationUrl($user, 'email-verification', $verificationToken);
        $emailData = [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'verification_link' => $verificationUrl
        ];
    
        dispatch(new SendVerificationEmail($user, $verificationUrl));
    
        return $this->success([], 'Verification email has been sent.', 200);
    }

    /**
     * Login API
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function signin(Request $request)
    {
        $this->authRequest->validateSignin($request);

        // // Normalize and hash the email from the request
        // $normalizedEmail = strtolower(trim($request->email));
        // $hashedEmail = hash('sha256', $normalizedEmail);

        // Query the database using the hashed email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('User not found', 404);
        }

        if (!app('hash')->check($request->password, $user->password)) {
            return $this->error('Password not matched', 401);
        }

        if (!$user->email_verified_at) {
            return $this->error('Email is not verified. Please verify your email.', 403);
        }

        if ($user->user_type == UserTypesEnum::Customer) 
        {
            // Check if a client already exists for this device
            $device_id = $request->device_id;
            $client = Client::where('name', $device_id)->first();

            if (!$client) {
                // Create a new client for this device
                $client = new Client;
                $client->name = $device_id;
                $client->secret = Str::random(40);
                $client->redirect = 'http://localhost';
                $client->personal_access_client = false;
                $client->password_client = true;
                $client->revoked = false;
                $client->user_id = $user->id;
                $client->save();
            }

            // Create session
            $sessionData = [
                'user_id' => $user->id,
                'device_id' => $device_id,
                'device_type' => $request->device_type,
                'timezone' => $this->getTimezoneFromIp($request->ip()),
                'login_time' => Carbon::now()
            ];
            $this->sessionService->setUserSession($sessionData);

            // Create access token
            $data = [
                'grant_type' => 'password',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'username' => $user->email,
                'password' => $request->password,
                'scope' => '',
            ];
        } 
        else 
        {
            $data = [
                'grant_type' => 'password',
                'client_id' => env('OAUTH_CLIENT_ID'),
                'client_secret' => env('OAUTH_CLIENT_SECRET'),
                'username' => $user->email,
                'password' => $request->password,
                'scope' => ''
            ];
        }
        
        return $this->passportService->handleOAuthRequest($data, 'Login successful');
    }

    /**
     * Refresh token API when original accessToken expires
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function refreshToken(Request $request)
    {
        $this->authRequest->validateRefreshToken($request);

        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => env('OAUTH_CLIENT_ID'),
            'client_secret' => env('OAUTH_CLIENT_SECRET'),
            'scope' => ''
        ];

        return $this->passportService->handleOAuthRequest($data, 'Token refreshed successfully');
    }

    /**
     * Verify Email & send
     * password reset link
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function forgotPassword(Request $request)
    {
        $this->authRequest->validateForgotPassword($request);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('User not found', 404);
        }

        // Create password reset token
        $token = Str::random(60);
        PasswordReset::create([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $resetLink = $this->generateVerificationUrl('reset-password', $token, $request->email);
        $emailData = [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'reset_link' => $resetLink
        ];

        dispatch(new SendPasswordResetEmail($emailData));

        return $this->success([], 'A password reset link has been sent to your email.', 201);
    }

    /**
     * Reset password
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function resetPassword(Request $request, $token, $email)
    {
        $this->authRequest->validateResetPassword($request);

        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->error('User not found', 404);
        }

        $passwordReset = PasswordReset::where('token', $token)
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            return $this->error('Invalid token.', 404);
        }

        $user->password = app('hash')->make($request->password);
        $user->setRememberToken(Str::random(60));
        $user->save();

        PasswordReset::where('token', $token)
            ->where('email', $email)
            ->delete();

        $emailData = [
            'customer_name' => $user->name,
            'customer_email' => $user->email
        ];

        dispatch(new SendPasswordChangedEmail($emailData));

        return $this->success([], 'Password has been successfully reset.', 200);
    }

    /**
     * Generate email verify/reset password link
     *
     * @param $user
     * @param $routeName
     * @param $token
     * @return string
     */
    private function generateVerificationUrl($routeName, $token, $email=null)
    {
        $environment = app()->environment();
        $config = config('custom');

        $baseUrl = $config['environments'][$environment]['base_url'] ?? $config['base_url'];

        if ($email) {
            return "{$baseUrl}/{$routeName}/{$token}/{$email}";
        }

        return "{$baseUrl}/{$routeName}/{$token}";
    }

    /**
     * Logout
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();

        return $this->passportService->clearCookies('Logout successful');
    }

    /**
     * Get user timezone from IP
     * Return a default timezone if the request fails or 
     * the timezone is not set
     * @param $ip
     * @return mixed
     */
    public function getTimezoneFromIp($ip) {
        $response = Http::get("http://ip-api.com/json/{$ip}?fields=timezone");

        if ($response->successful() && isset($response['timezone'])) {
            return $response['timezone'];
        } else {
            return 'UTC';
        }
    }

    /**
     * Forgot Parental Pin Email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotParentalPinEmail(Request $request) {
        $user = Auth::user();
        if(!$user) {
            return $this->error(array('User is not logged in'), 404);
        } else {
            $token = Str::random(60);
            $userId = $user->id;
            $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
            $expiredAt = Carbon::now('UTC')->addHours(24)->format('Y-m-d H:i:s');
            
            ParentalPinToken::create([
                'user_id' => $userId,
                'token' => $token,
                'created_at' => $createdAt,
                'expired_at' => $expiredAt
            ]);

            $verificationUrl = $this->generateVerificationUrl('forgot-parental-pin', $token);
            $emailData = [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'reset_link' => $verificationUrl
            ];

            dispatch(new SendResetParentalPinEmail($emailData));
    
            return $this->success([], 'Email sent successfully', 200);
        }

    }
}
