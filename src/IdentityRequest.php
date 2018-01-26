<?php

namespace Cerpus\AuthCore;


use GuzzleHttp\Client;

class IdentityRequest {
    private $config;
    private $accessToken;

    private $accessTokenManager = null;

    public function __construct(AuthServiceConfig $config, TokenResponse $accessToken) {
        $this->config = $config;
        $this->accessToken = $accessToken;
    }

    protected function httpClient() {
        return new Client();
    }

    public function execute() {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken->access_token
            ]
        ];
        $uri = $this->config->getUrl();
        if (substr($uri, -1) !== '/') {
            $uri .= '/';
        }

        $response = $this->httpClient()->get($uri.'v1/identity', $options);

        if ($response->getStatusCode() === 200) {
            $identity = json_decode($response->getBody());

            if (!isset($identity->identityId) || !$identity->identityId) {
                return null;
            }

            $identityObject = new IdentityResponse();
            $identityObject->identityId = $identity->identityId;
            $identityObject->firstName = (isset($identity->firstName) && !empty($identity->firstName) ? $identity->firstName : null);
            $identityObject->lastName = (isset($identity->lastName) && !empty($identity->lastName) ? $identity->lastName : null);
            $identityObject->displayName = (isset($identity->displayName) && !empty($identity->displayName) ? $identity->displayName : null);
            $identityObject->email = (isset($identity->email) && !empty($identity->email) ? $identity->email : null);
            $identityObject->notVerifiedEmails = isset($identity->notVerifiedEmails) && $identity->notVerifiedEmails ? $identity->notVerifiedEmails : [];
            $identityObject->additionalEmails = isset($identity->additionalEmails) && $identity->additionalEmails ? $identity->additionalEmails : [];
            return $identityObject;
        } else {
            return null;
        }
    }
}