<?php

namespace Cerpus\AuthCore;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class TestClientCredentialsTokenRequest extends ClientCredentialsTokenRequest {
    private $httpClient;

    public function __construct(Client $httpClient, AuthServiceConfig $config) {
        parent::__construct($config);
        $this->httpClient = $httpClient;
    }

    protected function httpClient(): Client {
        return $this->httpClient;
    }
}

class ClientCredentialsTokenRequestTest extends \PHPUnit_Framework_TestCase {
    public function testTokenRequest() {
        $json = json_encode([
            'access_token' => 'accesstoken',
            'expires_in' => 3600
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

        $tokenRequest = new TestClientCredentialsTokenRequest($client, $config);

        $response = $tokenRequest->execute();

        $this->assertNotNull($response);
        $this->assertEquals('accesstoken', $response->access_token);
        $this->assertNull($response->refresh_token);
        $this->assertEquals(3600, $response->expires_in);
        $this->assertNull($response->scope);

        $this->assertEquals(1, count($container));
        $requestResponse = $container[0];
        $request = $requestResponse['request'];
        $this->assertEquals('POST', $request->getMethod());
        $uri = $request->getUri()->__toString();
        $this->assertEquals('http://authtest.local/oauth/token', $uri);
        $this->assertEquals('client_id=clientid&grant_type=client_credentials', $request->getBody()->__toString());
    }
}