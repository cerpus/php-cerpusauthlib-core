<?php

namespace Cerpus\AuthCore;


use GuzzleHttp\Client;

abstract class TokenRequest {
    private $config;
    private $grantType;

    public function __construct(AuthServiceConfig $config, $grantType) {
        $this->config = $config;
        $this->grantType = $grantType;
    }

    protected function getParams() {
        return [
            'client_id' => $this->config->getClientId(),
            'grant_type' => $this->grantType
        ];
    }

    public abstract function getMethod();

    public function getUrl() {
        $url =  $this->config->getUrl();
        if (!substr($url, -1) !== '/') {
            $url .= '/';
        }
        return $url . 'oauth/token';
    }

    public abstract function getBodyParams();

    /**
     * @return \Cerpus\AuthCore\TokenResponse
     * @throws \Exception
     */
    public function execute() {
        $client = $this->httpClient();

        $method = strtolower($this->getMethod());

        $options = [
            'auth' => [$this->config->getClientId(), $this->config->getSecret()]
        ];
        if ($method === 'get') {
            $response = $client->get($this->getUrl(), $options);
        } else if ($method === 'post') {
            $options['form_params'] = $this->getBodyParams();
            $response = $client->post($this->getUrl(), $options);
        } else {
            throw new \Exception("Unexpected method for token request ".$method);
        }

        if ($response->getStatusCode() == 200) {
            $accessTokenResponseBody = $response->getBody()->getContents();
            $accessTokenResponse = json_decode($accessTokenResponseBody, false);

            $tokenResponse = new TokenResponse();
            $tokenResponse->access_token = $accessTokenResponse->access_token;
            $tokenResponse->refresh_token = isset($accessTokenResponse->refresh_token) && $accessTokenResponse->refresh_token ? $accessTokenResponse->refresh_token : null;
            $tokenResponse->expires_in = isset($accessTokenResponse->expires_in) && $accessTokenResponse->expires_in ? $accessTokenResponse->expires_in : null;
            $tokenResponse->scope = isset($accessTokenResponse->scope) && $accessTokenResponse->scope ? $accessTokenResponse->scope : null;
            return $tokenResponse;
        } else {
            return null;
        }
    }

    protected function httpClient(): Client {
        return new Client();
    }
}