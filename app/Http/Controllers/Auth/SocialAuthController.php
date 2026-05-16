<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialUser;
use Throwable;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver('google');
        return $driver->stateless()->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver     = Socialite::driver('google');
            $socialUser = $driver->stateless()->user();
        } catch (Throwable) {
            return redirect()->route('login')->with('error', 'Login Google gagal, silakan coba lagi.');
        }

        return $this->loginSocialUser($socialUser, 'google_id');
    }

    public function redirectToSso(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver('sso');
        return $driver->scopes(config('services.sso.scopes', []))->redirect();
    }

    public function handleSsoCallback(): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver('sso')->user();
        } catch (Throwable) {
            return redirect()->route('login')->with('error', 'Login SSO gagal, silakan coba lagi.');
        }

        return $this->loginSocialUser($socialUser, 'sso_id');
    }

    private function loginSocialUser(SocialUser $socialUser, string $idField): RedirectResponse
    {
        $user = $this->findOrCreateUser($socialUser, $idField);

        if (! $user->is_active) {
            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan. Hubungi administrator.');
        }

        Auth::login($user, remember: true);
        $user->update([
            'last_login_at' => now(),
            'login_method'  => match ($idField) {
                'google_id' => 'google',
                'sso_id'    => 'sso',
                default     => 'password',
            },
        ]);
        request()->session()->regenerate();

        return redirect()->intended('/');
    }

    private function findOrCreateUser(SocialUser $socialUser, string $idField): User
    {
        // 1. Find by provider ID (returning user)
        $user = User::firstWhere($idField, $socialUser->getId());
        if ($user) {
            return $user;
        }

        // 2. Find by email — link existing account to this provider
        if ($socialUser->getEmail()) {
            $user = User::firstWhere('email', $socialUser->getEmail());
            if ($user) {
                $user->update([$idField => $socialUser->getId()]);
                return $user;
            }
        }

        // 3. Create new user directly (bypasses pending_registrations)
        return User::create([
            $idField    => $socialUser->getId(),
            'name'      => $socialUser->getName() ?? 'User',
            'email'     => $socialUser->getEmail(),
            'avatar'    => $socialUser->getAvatar(),
            'is_active' => true,
        ]);
    }
}
