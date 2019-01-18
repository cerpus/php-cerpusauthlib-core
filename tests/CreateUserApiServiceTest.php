<?php
/**
 * Created by PhpStorm.
 * User: janeover
 * Date: 17.01.19
 * Time: 15:14
 */

namespace Cerpus\AuthCore;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class CreateUserApiServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testSetCountry() {
        $json = "{\"alpha2\": \"NO\", \"alpha3\": \"NOR\"}";
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

        $service = new CreateUserApiService($config, $client);

        $service->setCountry("cd1acb3f-5900-4f6a-9a12-d90a4f7c32f3", "NO");

        $this->assertTrue(true);
    }

    public function testGetCountryNull() {
        $json = "{\"identity\":{\"id\":\"cd1acb3f-5900-4f6a-9a12-d90a4f7c32f3\",\"usernames\":[],\"firstName\":\"First\",\"lastName\":\"Last\",\"displayName\":\"Display Name\",\"email\":\"test@example.com\",\"country\":{\"alpha2\":null,\"alpha3\":null}}}";
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

        $service = new CreateUserApiService($config, $client);

        $country = $service->getCountry('cd1acb3f-5900-4f6a-9a12-d90a4f7c32f3');

        $this->assertNull($country);
    }

    public function testGetCountry() {
        $json = "{\"identity\":{\"id\":\"cd1acb3f-5900-4f6a-9a12-d90a4f7c32f3\",\"usernames\":[],\"firstName\":\"First\",\"lastName\":\"Last\",\"displayName\":\"Display Name\",\"email\":\"test@example.com\",\"country\":{\"alpha2\":\"NO\",\"alpha3\":\"NOR\"}}}";
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

        $service = new CreateUserApiService($config, $client);

        $country = $service->getCountry('cd1acb3f-5900-4f6a-9a12-d90a4f7c32f3');

        $this->assertEquals('NO', $country);
    }
}
