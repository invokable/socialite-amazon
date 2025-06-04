<?php

namespace Revolution\Socialite\Amazon\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\User;
use Mockery as m;
use Revolution\Socialite\Amazon\AmazonProvider;

class AmazonProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_redirect_generates_correct_url()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->once();

        $provider = new AmazonProvider($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $url = $response->getTargetUrl();
        $this->assertStringStartsWith('https://www.amazon.com/ap/oa', $url);
        $this->assertStringContainsString('client_id=client_id', $url);
        $this->assertStringContainsString('redirect_uri=redirect', $url);
        $this->assertStringContainsString('scope=profile', $url);
        $this->assertStringContainsString('response_type=code', $url);
    }

    public function test_redirect_with_custom_scopes()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->once();

        $provider = new AmazonProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->scopes(['profile', 'postal_code']);
        $response = $provider->redirect();

        $url = $response->getTargetUrl();
        $this->assertStringContainsString('scope=profile+postal_code', $url);
    }

    public function test_user_retrieval_with_mocked_http_client()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('A', 40));

        $provider = new AmazonProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_456',
            'expires_in' => 3600,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'user_id' => 'amazon_user_123',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('amazon_user_123', $user->getId());
        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals('John Doe', $user->getNickname());
        $this->assertEquals('john@example.com', $user->getEmail());
        $this->assertEquals('', $user->getAvatar());
        $this->assertEquals('access_token_123', $user->token);
        $this->assertEquals('refresh_token_456', $user->refreshToken);
        $this->assertEquals(3600, $user->expiresIn);
    }

    public function test_user_retrieval_with_missing_optional_fields()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('B', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('B', 40));

        $provider = new AmazonProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'access_token_456',
            'expires_in' => 7200,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'user_id' => 'amazon_user_456',
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('amazon_user_456', $user->getId());
        $this->assertEquals('', $user->getName());
        $this->assertEquals('', $user->getNickname());
        $this->assertEquals('', $user->getEmail());
        $this->assertEquals('', $user->getAvatar());
        $this->assertEquals('access_token_456', $user->token);
        $this->assertNull($user->refreshToken);
        $this->assertEquals(7200, $user->expiresIn);
    }

    public function test_user_retrieval_with_partial_data()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('C', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('C', 40));

        $provider = new AmazonProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'access_token_789',
            'refresh_token' => 'refresh_token_789',
            'expires_in' => 1800,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'user_id' => 'amazon_user_789',
            'name' => 'Jane Smith',
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('amazon_user_789', $user->getId());
        $this->assertEquals('Jane Smith', $user->getName());
        $this->assertEquals('Jane Smith', $user->getNickname());
        $this->assertEquals('', $user->getEmail());
        $this->assertEquals('', $user->getAvatar());
    }

    public function test_scopes_configuration()
    {
        $request = Request::create('foo');
        $provider = new AmazonProvider($request, 'client_id', 'client_secret', 'redirect');

        $this->assertEquals(['profile'], $provider->getScopes());
    }

    public function test_provider_with_custom_scopes()
    {
        $request = Request::create('foo');
        $provider = new AmazonProvider($request, 'client_id', 'client_secret', 'redirect');

        $provider->scopes(['profile', 'postal_code']);

        $this->assertEquals(['profile', 'postal_code'], $provider->getScopes());
    }

    public function test_user_profile_request_uses_bearer_token()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('D', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('D', 40));

        $provider = new AmazonProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'bearer_token_test',
            'expires_in' => 3600,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'user_id' => 'test_user_id',
            'name' => 'Test User',
            'email' => 'test@amazon.com',
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test_user_id', $user->getId());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('test@amazon.com', $user->getEmail());
        $this->assertEquals('bearer_token_test', $user->token);
    }

    public function test_scope_separator_is_space()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->once();

        $provider = new AmazonProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->scopes(['profile', 'postal_code', 'user:email']);
        $response = $provider->redirect();

        $url = $response->getTargetUrl();
        $this->assertStringContainsString('scope=profile+postal_code+user%3Aemail', $url);
    }
}
