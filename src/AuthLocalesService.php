<?php

namespace Cerpus\AuthCore;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AuthLocalesService {
    private $config;

    public function __construct(AuthServiceConfig $config) {
        $this->config = $config;
    }

    /**
     * @return Client
     */
    public function httpClient()
    {
        $authServer = $this->config->getUrl();
        $client = new Client(
            [
                'base_uri' => $authServer,
            ]
        );

        return $client;
    }

    public function getAuthLocalesInfo() {
        try {
            $response = $this->httpClient()->get('/v1/locales');
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            $body = $response->getBody()->getContents();
            return json_decode($body);
        } else {
            return null;
        }
    }
}