<?php

namespace Cerpus\AuthCore;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class CreateUserApiService
{
    private $config;

    public function __construct(AuthServiceConfig $config) {
        $this->config = $config;
    }

    /**
     * @return Client
     * @throws \Cerpus\AuthCore\AuthAccessDeniedException
     */
    public function httpClient()
    {
        static $client = null;

        if ($client === null) {
            $authServer = $this->config->getUrl();
            $tokenResponse = (new ClientCredentialsTokenRequest($this->config))->execute();
            if (!$tokenResponse) {
                throw new AuthAccessDeniedException();
            }
            $token = $tokenResponse->access_token;
            $client = new Client(
                [
                    'base_uri' => $authServer,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token
                    ]
                ]
            );
        }

        return $client;
    }

    /**
     * @param $email
     *
     * @return mixed|null
     * @throws \Cerpus\AuthCore\AuthAccessDeniedException
     */
    public function getUserByEmail($email) {
        try {
            $response = $this->httpClient()->get('/v1/users?email='.rawurlencode($email));
            $body = $response->getBody()->getContents();
            $json = json_decode($body);
            return $json;
        } catch (ClientException $clientException) {
            if ($clientException->getCode() == 403 || $clientException->getCode() == 401) {
                throw new AuthAccessDeniedException("Get user by email access denied, code: ".$clientException->getCode());
            } else {
                return null;
            }
        }
    }

    /**
     * @param $email
     *
     * @return mixed|null
     * @throws \Cerpus\AuthCore\AuthAccessDeniedException
     * @throws \Cerpus\AuthCore\AuthConflictException
     */
    public function createEmailCredentials($email) {
        try {
            $params = ['email' => $email];
            $response = $this->httpClient()->post('/v1/users/email', [
                'form_params' => $params
            ]);
            $body = $response->getBody()->getContents();
            $json = json_decode($body);
            return $json;
        } catch (ClientException $clientException) {
            if ($clientException->getCode() == 403 || $clientException->getCode() == 401) {
                throw new AuthAccessDeniedException("Create email credentials, code: " . $clientException->getCode());
            } else if ($clientException->getCode() == 409) {
                throw new AuthConflictException("Create email credentials conflict");
            } else {
                return null;
            }
        }
    }

    /**
     * @param $identity
     * @param $email
     * @param $verified
     *
     * @return mixed
     * @throws \Cerpus\AuthCore\AuthAccessDeniedException
     * @throws \Cerpus\AuthCore\AuthConflictException
     */
    public function addEmailCredentialsToIdentity($identity, $email, $verified) {
        try {
            $params = ['email' => $email, 'verified' => ($verified ? 'true' : 'false')];
            $response = $this->httpClient()->post('/v1/users/'.rawurlencode($identity).'/emails', [
                'form_params' => $params
            ]);
            $body = $response->getBody()->getContents();
            $json = json_decode($body);
            return $json;
        } catch (ClientException $clientException) {
            if ($clientException->getCode() == 403 || $clientException->getCode() == 401) {
                throw new AuthAccessDeniedException("Create email credentials, code: " . $clientException->getCode());
            } else if ($clientException->getCode() == 409) {
                throw new AuthConflictException("Create email credentials conflict");
            } else {
                throw new \Exception("Create user api error", $clientException->getCode(), $clientException);
            }
        }
    }

    /**
     * @param $identity
     *
     * @return mixed
     * @throws \Cerpus\AuthCore\AuthAccessDeniedException
     */
    public function getIdentity($identity) {
        try {
            $response = $this->httpClient()->get('/v1/users/'.rawurlencode($identity));
            $body = $response->getBody()->getContents();
            $json = json_decode($body);
            return $json;
        } catch (ClientException $clientException) {
            if ($clientException->getCode() == 403 || $clientException->getCode() == 401) {
                throw new AuthAccessDeniedException("Get identity, code: " . $clientException->getCode());
            } else {
                throw new \Exception("Get identity error", $clientException->getCode(), $clientException);
            }
        }
    }

    /**
     * @param $identity
     *
     * @return mixed
     * @throws \Cerpus\AuthCore\AuthAccessDeniedException
     */
    public function getIdentityEmails($identity) {
        try {
            $response = $this->httpClient()->get('/v1/users/'.rawurlencode($identity).'/emails');
            $body = $response->getBody()->getContents();
            $json = json_decode($body);
            return $json;
        } catch (ClientException $clientException) {
            if ($clientException->getCode() == 403 || $clientException->getCode() == 401) {
                throw new AuthAccessDeniedException("Get identity emails, code: " . $clientException->getCode());
            } else {
                throw new \Exception("Get identity emails error", $clientException->getCode(), $clientException);
            }
        }
    }
}