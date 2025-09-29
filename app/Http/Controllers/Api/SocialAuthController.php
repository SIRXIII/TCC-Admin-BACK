<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    use ApiResponse;

    /**
     * Redirect to social provider
     */
    // public function redirectToProvider($provider, Request $request)
    // {
    //     $this->validateProvider($provider);

    //     try {
    //         // Generate a state parameter for security
    //         $state = Str::random(40);

    //         // Store state in cache for verification (expires in 10 minutes)
    //         Cache::put("social_state_{$state}", [
    //             'provider' => $provider,
    //             'created_at' => now()
    //         ], now()->addMinutes(10));

    //         // Use stateless mode for API with custom state
    //         $redirectUrl = Socialite::driver($provider)
    //             ->stateless()
    //             ->with(['state' => $state])
    //             ->redirect()
    //             ->getTargetUrl();

    //         return $this->success([
    //             'redirect_url' => $redirectUrl,
    //             'state' => $state
    //         ], "Redirect to {$provider} authentication", 200);
    //     } catch (\Exception $e) {
    //         return $this->error("Failed to redirect to {$provider}", $e->getMessage(), 500);
    //     }
    // }
    public function redirectToProvider($provider, Request $request)
{
    $this->validateProvider($provider);

    try {
        $state = Str::random(40);

        Cache::put("social_state_{$state}", [
            'provider' => $provider,
            'created_at' => now()
        ], now()->addMinutes(10));

        // Force Socialite to use SPA redirect instead of API
        $redirectUrl = Socialite::driver($provider)
            ->stateless()
            ->with(['state' => $state])
            ->redirectUrl(config('app.spa_url') . '/oauth/callback') 
            ->redirect()
            ->getTargetUrl();

        return $this->success([
            'redirect_url' => $redirectUrl,
            'state' => $state
        ], "Redirect to {$provider} authentication", 200);

    } catch (\Exception $e) {
        return $this->error("Failed to redirect to {$provider}", $e->getMessage(), 500);
    }
}


    /**
     * Handle social provider callback
     */
    public function handleProviderCallback($provider, Request $request)
    {
        $this->validateProvider($provider);

        try {

            if ($request->has('state')) {
                $stateData = Cache::get("social_state_{$request->state}");
                if (!$stateData || $stateData['provider'] !== $provider) {

                    $frontendUrl = config('app.spa_url');

                    return redirect()->away("{$frontendUrl}/login?error=invalid_state");
                }

                Cache::forget("social_state_{$request->state}");
            }

            $socialUser = Socialite::driver($provider)->stateless()->user();

            $user = $this->findOrCreateUser($socialUser, $provider);

            $token = $user->createToken('api')->plainTextToken;

            return $this->success([
                'user' => new UserResource($user),
                'token' => $token
            ], 'Social login successful', 200);

        } catch (\Exception $e) {
            return $this->error('Social authentication failed', $e->getMessage(), 422);
        }
    }

    /**
     * Handle social login with access token (for mobile apps)
     */
    public function loginWithToken(Request $request, $provider)
    {
        $this->validateProvider($provider);

        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            // Get user from provider using access token
            $socialUser = Socialite::driver($provider)->userFromToken($request->access_token);

            // Find or create user
            $user = $this->findOrCreateUser($socialUser, $provider);

            // Generate token
            $token = $user->createToken('api')->plainTextToken;

            return $this->success([
                'user' => new UserResource($user),
                'token' => $token,
            ], 'Social login successful', 200);

        } catch (\Exception $e) {
            return $this->error("Social authentication failed", $e->getMessage(), 422);
        }
    }

    /**
     * Find or create user from social provider
     */
    private function findOrCreateUser($socialUser, $provider)
    {
        // Parse name from social user
        $nameData = $this->parseUserName($socialUser->getName());

        // First, try to find user by provider and provider_id
        $user = User::where('provider', $provider)
                   ->where('provider_id', $socialUser->getId())
                   ->first();

        if ($user) {
            // Update user info including name fields
            $user->update([
                'first_name' => $nameData['first_name'],
                'last_name' => $nameData['last_name'],
                'provider_token' => $socialUser->token ?? null,
                'provider_refresh_token' => $socialUser->refreshToken ?? null,
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
            ]);
            return $user;
        }

        // Try to find user by email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link social account to existing user and update name fields
            $user->update([
                'first_name' => $nameData['first_name'],
                'last_name' => $nameData['last_name'],
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'provider_token' => $socialUser->token ?? null,
                'provider_refresh_token' => $socialUser->refreshToken ?? null,
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
            ]);
            return $user;
        }

        // Create new user
        return User::create([
            'first_name' => $nameData['first_name'],
            'last_name' => $nameData['last_name'],
            'email' => $socialUser->getEmail(),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'provider_token' => $socialUser->token ?? null,
            'provider_refresh_token' => $socialUser->refreshToken ?? null,
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(), // Social accounts are considered verified
        ]);
    }

    /**
     * Parse user name into first name, last name, and full name
     */
    private function parseUserName($fullName)
    {
        if (empty($fullName)) {
            return [
                'first_name' => 'Social',
                'last_name' => 'User'
            ];
        }

        $nameParts = explode(' ', trim($fullName));

        if (count($nameParts) === 1) {
            return [
                'first_name' => $nameParts[0],
                'last_name' => ''
            ];
        }

        $firstName = $nameParts[0];
        $lastName = implode(' ', array_slice($nameParts, 1));

        return [
            'first_name' => $firstName,
            'last_name' => $lastName
        ];
    }

    /**
     * Validate social provider
     */
    private function validateProvider($provider)
    {
        $allowedProviders = ['google', 'apple', 'shopify'];

        if (!in_array($provider, $allowedProviders)) {
            abort(422, 'Invalid social provider');
        }
    }

    /**
     * Unlink social account
     */
    public function unlinkSocialAccount(Request $request)
    {
        $user = $request->user();

        if (!$user->provider) {
            return $this->error('No social account linked', null, 422);
        }

        // Don't allow unlinking if user has no password (social-only account)
        if (!$user->password) {
            return $this->error('Cannot unlink social account. Please set a password first.', null, 422);
        }

        $user->update([
            'provider' => null,
            'provider_id' => null,
            'provider_token' => null,
            'provider_refresh_token' => null,
        ]);

        return $this->success([
            'user' => new UserResource($user)
        ], 'Social account unlinked successfully', 200);
    }

    /**
     * Get available social providers
     */
    public function getProviders()
    {
        return $this->success([
            'providers' => [
                'google' => [
                    'name' => 'Google',
                    'enabled' => !empty(config('services.google.client_id')),
                ],
                'apple' => [
                    'name' => 'Apple',
                    'enabled' => !empty(config('services.apple.client_id')),
                ],
                'shopify' => [
                    'name' => 'Shopify',
                    'enabled' => !empty(config('services.shopify.client_id')),
                ],
            ]
        ], 'Available social providers', 200);
    }
}
