<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Laravel\Socialite\Two\User as SocialiteUser;

class SocialLoginController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            /** @var SocialiteUser $googleUser */
            $googleUser = Socialite::driver('google')->user();

            $user = User::updateOrCreate(
                ['google_id' => $googleUser->id],
                [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_token' => $googleUser->token,
                    'password' => bcrypt(Str::random(24)),
                ]
            );

            Auth::login($user);

            return redirect()->intended(route('home'));
        } catch (\Exception $e) {
            Log::error('Google authentication failed: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Google authentication failed');
        }
    }

    public function redirectToFacebook(): RedirectResponse
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback(): RedirectResponse
    {
        try {
            /** @var SocialiteUser $facebookUser */
            $facebookUser = Socialite::driver('facebook')->user();

            $user = User::updateOrCreate(
                ['facebook_id' => $facebookUser->id],
                [
                    'name' => $facebookUser->name,
                    'email' => $facebookUser->email,
                    'facebook_token' => $facebookUser->token,
                    'password' => bcrypt(Str::random(24)),
                ]
            );

            Auth::login($user);

            return redirect()->intended(route('home'));
        } catch (\Exception $e) {
            Log::error('Facebook authentication failed: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Facebook authentication failed');
        }
    }
}
