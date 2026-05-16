<?php

namespace App\Socialite;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class SsoProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(config('services.sso.auth_url'), $state);
    }

    protected function getTokenUrl(): string
    {
        return config('services.sso.token_url');
    }

    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(config('services.sso.user_url'), [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id'     => Arr::get($user, 'sub', Arr::get($user, 'id')),
            'name'   => Arr::get($user, 'name'),
            'email'  => Arr::get($user, 'email'),
            'avatar' => Arr::get($user, 'picture', Arr::get($user, 'avatar')),
        ]);
    }
}
