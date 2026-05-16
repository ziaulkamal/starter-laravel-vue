<?php

namespace App\Providers;

use App\Socialite\SsoProvider;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers()->symbols());

        Socialite::extend('sso', function ($app) {
            $config = $app['config']['services.sso'];
            return Socialite::buildProvider(SsoProvider::class, $config);
        });
    }
}
