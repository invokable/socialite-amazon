<?php

namespace Revolution\Socialite\Amazon\Tests;

use Laravel\Socialite\SocialiteServiceProvider;
use Revolution\Socialite\Amazon\AmazonServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            SocialiteServiceProvider::class,
            AmazonServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            //
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('services.amazon',
            [
                'client_id' => 'test',
                'client_secret' => 'test',
                'redirect' => 'http://localhost',
            ]
        );
    }
}
