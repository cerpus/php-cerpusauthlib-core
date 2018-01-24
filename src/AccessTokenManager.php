<?php

namespace Cerpus\AuthCore;

use Cerpus\AuthCore\utils\SessionWrapper;
use GuzzleHttp\Client;

class AccessTokenManager {
    private $config;
    private $integration;
    private $session;

    public function __construct(AuthServiceConfig $config, AuthCoreIntegration $integration) {
        $this->config = $config;
        $this->integration = $integration;
        $this->session = new SessionWrapper($integration->session());
    }

    public function getAccessToken() {
        $accessToken = $this->session->get('CERPUS_AUTH_ACCESS_TOKEN');
        $accessTokenExpiry = $this->session->get('CERPUS_AUTH_EXPIRES_AT');
        $refreshToken = $this->session->get('CERPUS_AUTH_REFRESH_TOKEN');
        if ($accessTokenExpiry && ($accessTokenExpiry - 600) < time() && $refreshToken) {
            /*
             * Expired or less than 10 minutes from expiring.
             * Considered expired .. use the refresh token
             */

            $authServer = $this->config->getUrl();
            $authUser = $this->config->getClientId();
            $authSecret = $this->config->getSecret();

            $client = new Client(['base_uri' => $authServer]);

            $uri = '/oauth/token';
            $options = [
                'auth' => [$authUser, $authSecret],
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken
                ]
            ];

            $response = $client->post($uri, $options);

            if ($response->getStatusCode() == 200) {
                $accessTokenResponseBody = $response->getBody()->getContents();
                $accessTokenResponse = json_decode($accessTokenResponseBody, false);

                $accessToken = $accessTokenResponse->access_token;
                $refreshToken = $accessTokenResponse->refresh_token ? $accessTokenResponse->refresh_token : $refreshToken;
                $expiresIn = $accessTokenResponse->expires_in ? $accessTokenResponse->expires_in : null;
                $scope = $accessTokenResponse->scope ? $accessTokenResponse->scope : null;
                if ($expiresIn) {
                    $expiresAt = time() + $expiresIn - 10;
                } else {
                    $expiresAt = null;
                }

                AccessTokenManager::setAccessToken($accessToken, $refreshToken, $expiresAt, $scope);
            }
        }
        return $accessToken;
    }

    public function setAccessToken($accessToken, $refreshToken=null, $expiresAt=null, $scope=null) {
        $this->session->putAll([
            'CERPUS_AUTH_ACCESS_TOKEN' => $accessToken,
            'CERPUS_AUTH_REFRESH_TOKEN' => $refreshToken,
            'CERPUS_AUTH_EXPIRES_AT' => $expiresAt,
            'CERPUS_AUTH_SCOPE' => $scope
        ]);
    }
}