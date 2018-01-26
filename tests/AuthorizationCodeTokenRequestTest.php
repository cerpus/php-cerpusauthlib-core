<?php

namespace Cerpus\AuthCore;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class TestAuthorizationCodeTokenRequest extends AuthorizationCodeTokenRequest {
    private $httpClient;

    public function __construct(Client $httpClient, AuthServiceConfig $config, $redirectUri, $code) {
        parent::__construct($config, $redirectUri, $code);
        $this->httpClient = $httpClient;
    }

    protected function httpClient(): Client {
        return $this->httpClient;
    }
}

class AuthorizationCodeTokenRequestTest extends \PHPUnit_Framework_TestCase {
    public function testAuthorizeRequest() {
        $json = json_encode([
            'access_token' => 'accesstoken',
            'refresh_token' => 'refreshtoken',
            'expires_in' => 3600,
            'scope' => 'openid'
        ]);
        $mock = new MockHandler([
            new Response(200, ['Content-Type', 'application/json'], $json),
            new RequestException("Error Communicating with Server", new Request('GET', 'test'))
        ]);

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $config = (new AuthServiceConfig())
            ->setUrl('http://authtest.local')
            ->setClientId('clientid')
            ->setSecret('secret');

        $authorizeRequest = new TestAuthorizationCodeTokenRequest($client, $config, 'http://testapp.local/return', 'testcode');

        $response = $authorizeRequest->execute();

        $this->assertNotNull($response);
        $this->assertEquals('accesstoken', $response->access_token);
        $this->assertEquals('refreshtoken', $response->refresh_token);
        $this->assertEquals(3600, $response->expires_in);
        $this->assertEquals('openid', $response->scope);

        $this->assertEquals(1, count($container));
        $requestResponse = $container[0];
        $request = $requestResponse['request'];
        $this->assertEquals('GET', $request->getMethod());
        $uri = $request->getUri()->__toString();
        $this->assertEquals('http://authtest.local/oauth/token?client_id=clientid&grant_type=authorization_code&redirect_uri=http%3A%2F%2Ftestapp.local%2Freturn&code=testcode', $uri);
    }
}