<?php

namespace Revolution\Socialite\Amazon\Tests;

use Illuminate\Foundation\Application;
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
     * @param  Application  $app
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
