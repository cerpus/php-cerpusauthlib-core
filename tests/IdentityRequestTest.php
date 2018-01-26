<?php

namespace Cerpus\AuthCore;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class TestIdentityRequest extends IdentityRequest {
    private $httpClient;

    public function __construct(Client $httpClient, AuthServiceConfig $config, TokenResponse $accessToken) {
        parent::__construct($config, $accessToken);
        $this->httpClient = $httpClient;
    }

    protected function httpClient(): Client {
        return $this->httpClient;
    }
}

class IdentityRequestTest extends \PHPUnit_Framework_TestCase {
    public function testIdentityRequest() {
        $json = json_encode([
            'identityId' => 'testid',
            'firstName' => 'First',
            'lastName' => 'Last',
            'displayName' => 'Display Name',
            'email' => 'test@example.com',
            'additionalEmails' => [],
            'notVerifiedEmails' => []
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
            ->setUrl('http://authtest.local');

        $tokenResponse = new TokenResponse();
        $tokenResponse->access_token = 'accesstoken';
        $tokenRequest = new TestIdentityRequest($client, $config, $tokenResponse);

        $response = $tokenRequest->execute();

        $this->assertNotNull($response);
        $this->assertEquals('testid', $response->identityId);
        $this->assertEquals('First', $response->firstName);
        $this->assertEquals('Last', $response->lastName);
        $this->assertEquals('Display Name', $response->displayName);
        $this->assertEquals('test@example.com', $response->email);
        $this->assertEquals([], $response->additionalEmails);
        $this->assertEquals([], $response->notVerifiedEmails);

        $this->assertEquals(1, count($container));
        $requestResponse = $container[0];
        $request = $requestResponse['request'];
        $this->assertEquals('GET', $request->getMethod());
        $uri = $request->getUri()->__toString();
        $this->assertEquals('http://authtest.local/v1/identity', $uri);
    }
}