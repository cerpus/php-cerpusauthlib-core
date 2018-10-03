<?php

namespace Cerpus\AuthCore;

use GuzzleHttp\Client;

class CheckTokenRequest {
    /** @var \Cerpus\AuthCore\AuthServiceConfig */
    private $config;
    /** @var string */
    private $token;

    public function __construct(\Cerpus\AuthCore\AuthServiceConfig $config, string $token) {
        $this->config = $config;
        $this->token = $token;
    }

    protected function getUrl() {
        $url =  $this->config->getUrl();
        if (!substr($url, -1) !== '/') {
            $url .= '/';
        }
        return $url . 'oauth/check_token';
    }

    /**
     * @return \Cerpus\AuthCore\CheckTokenResponse|null
     */
    public function execute() {
        $client = $this->httpClient();

        $options = [
            'auth' => [$this->config->getClientId(), $this->config->getSecret()],
            'form_params' => [
                'token' => $this->token
            ]
        ];

        $response = $client->post($this->getUrl(), $options);

        if ($response->getStatusCode() == 200) {
            $checkTokenResponseBody = $response->getBody()->getContents();
            $checkTokenResponse = json_decode($checkTokenResponseBody, FALSE);
            $checkToken = new CheckTokenResponse();
            $checkToken->setGrantType($checkTokenResponse->grant_type);
            $checkToken->setScope($checkTokenResponse->scope);
            $checkToken->setActive($checkTokenResponse->active);
            $checkToken->setExpiry($checkTokenResponse->exp);
            $checkToken->setAuthorities(isset($checkTokenResponse->authorities) ? $checkTokenResponse->authorities : []);
            $checkToken->setClientId($checkTokenResponse->client_id);
            return $checkToken;
        } else {
            return null;
        }
    }

    protected function httpClient(): Client {
        return new Client();
    }
}