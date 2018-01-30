<?php

namespace Cerpus\AuthCore;

use GuzzleHttp\Client;

class JWTService {
    private $config;
    private $integration;

    public function __construct(AuthServiceConfig $config, AuthCoreIntegration $integration) {
        $this->config = $config;
        $this->integration = $integration;
    }

    public function getJwt() {
        $accessToken = (new AccessTokenManager($this->config, $this->integration))->getAccessToken();
        if ($accessToken) {
            $authServer = $this->config->getUrl();

            $client = new Client(['base_uri' => $authServer]);

            $uri = '/v1/jwt/create';
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken
                ]
            ];

            $response = $client->post($uri, $options);

            if ($response->getStatusCode() == 200) {
                $tokenResponse = json_decode($response->getBody()
                    ->getContents(), FALSE);
                return $tokenResponse->token;
            }
        }
        return null;
    }
}