<?php

namespace Cerpus\AuthCore;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class TestCheckTokenRequest extends CheckTokenRequest {
    private $client;

    protected function httpClient(): Client {
        return $this->client;
    }

    public function __construct(
        Client $httpClient,
        \Cerpus\AuthCore\AuthServiceConfig $config,
        string $token
    ) {
        parent::__construct($config, $token);
        $this->client = $httpClient;
    }
}

class CheckTokenRequestTest extends \PHPUnit_Framework_TestCase {
    public function testCheckToken() {
        $responseBody = "{\"grant_type\":\"authorization_code\",\"user_name\":\"f18d335c-3c57-4d1a-8dc3-3e585af824a0\",\"scope\":[\"read\"],\"active\":true,\"exp\":1538221600,\"authorities\":[\"ROLE_USER\"],\"client_id\":\"authoauthlocal\"}";
        $mock = new MockHandler([
            new Response(200, ['Content-Type', 'application/json'], $responseBody),
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

        $token = "testtoken";

        $testCheckTokenRequest = new TestCheckTokenRequest($client, $config, $token);

        $response = $testCheckTokenRequest->execute();

        $this->assertNotNull($response);
        $this->assertEquals("authorization_code", $response->getGrantType());
        $this->assertEquals(1, count($response->getScope()));
        $this->assertTrue(in_array("read", $response->getScope()));
        $this->assertEquals(1538221600, $response->getExpiry());
        $this->assertEquals(1, count($response->getAuthorities()));
        $this->assertTrue(in_array("ROLE_USER", $response->getAuthorities()));
        $this->assertEquals("authoauthlocal", $response->getClientId());

        $this->assertEquals(1, count($container));
        $requestResponse = $container[0];
        /** @var Request $request */
        $request = $requestResponse['request'];
        $this->assertEquals('POST', $request->getMethod());
        $uri = $request->getUri()->__toString();
        $this->assertEquals('http://authtest.local/oauth/check_token', $uri);
        $this->assertEquals('token=testtoken', $request->getBody()->__toString());
        $this->assertEquals(1, count($request->getHeader('Authorization')));
        $this->assertEquals('Basic '.base64_encode("clientid:secret"), $request->getHeader('Authorization')[0]);
    }
}