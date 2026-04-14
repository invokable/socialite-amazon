<?php

namespace Revolution\Socialite\Amazon\Tests;

use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Revolution\Socialite\Amazon\AmazonProvider;

class SocialiteTest extends TestCase
{
    public function test_instance()
    {
        $provider = Socialite::driver('amazon');

        $this->assertInstanceOf(AmazonProvider::class, $provider);
    }

    public function test_redirect()
    {
        Socialite::fake('amazon');

        $response = Socialite::driver('amazon')->redirect();

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_user()
    {
        Socialite::fake('amazon', (new User)->map([
            'id' => 'foo',
            'name' => 'name',
            'email' => 'email@example.com',
        ])->setToken('access_token')
            ->setRefreshToken('refresh_token')
            ->setExpiresIn(3600));

        $user = Socialite::driver('amazon')->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('foo', $user->getId());
        $this->assertSame('access_token', $user->token);
        $this->assertSame('refresh_token', $user->refreshToken);
        $this->assertSame('name', $user->getName());
        $this->assertSame('email@example.com', $user->getEmail());
        $this->assertSame(3600, $user->expiresIn);
    }
}
