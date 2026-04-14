<?php

namespace Revolution\Socialite\Amazon\Tests;

use GuzzleHttp\Client;
use Mockery as m;
use Mockery\MockInterface;
use Revolution\Socialite\Amazon\AmazonProvider;
use stdClass;

class AmazonProviderStub extends AmazonProvider
{
    /**
     * @var Client|MockInterface
     */
    public $http;

    protected function getAuthUrl($state)
    {
        return 'http://auth.url';
    }

    protected function getTokenUrl()
    {
        return 'http://token.url';
    }

    protected function getUserByToken($token)
    {
        return [
            'user_id' => 'foo',
            'name' => 'name',
            'email' => 'email',
        ];
    }

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     *
     * @return Client|MockInterface
     */
    protected function getHttpClient()
    {
        if ($this->http) {
            return $this->http;
        }

        return $this->http = m::mock(stdClass::class);
    }
}
