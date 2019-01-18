<?php

namespace Cerpus\AuthCore;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class CreateUserApiService
{
    private $client = null;
    private $config;

    /**
     * CreateUserApiService constructor.
     * @param AuthServiceConfig $config
     * @param Client|null $httpClient
     */
    public function __construct(AuthServiceConfig $config, $httpClient = null) {
        $this->config = $config;
        $this->client = $httpClient;
    }

    /**
     * @return Client
     * @throws \Cerpus\AuthCore\AuthAccessDeniedException
     */
    public function httpClient()
    {
        if ($this->client === null) {
            $authServer = $this->config->getUrl();
            $tokenResponse = (new ClientCredentialsTokenRequest($this->config))->execute();
            if (!$tokenResponse) {
                throw new AuthAccessDeniedException();
            }
            $token = $tokenResponse->access_token;
            $this->client = new Client(
                [
                    'base_uri' => $authServer,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token
                    ]
                ]
            );
        }

        return $this->client;
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

    public function setCountry($identity, $countryCode) {
        try {
            $response = $this->httpClient()->post('/v1/users/'.rawurlencode($identity).'/country', [
                'form_params' => [
                    'country' => $countryCode
                ]
            ]);
            $body = $response->getBody()->getContents();
            $json = json_decode($body);
            if (strtoupper($json->alpha2) !== strtoupper($countryCode) && strtoupper($json->alpha3) !== strtoupper($countryCode)) {
                throw new \Exception("Failed to set country. Bad response");
            }
        } catch (ClientException $clientException) {
            if ($clientException->getCode() == 403 || $clientException->getCode() == 401) {
                throw new AuthAccessDeniedException("Set country, code: " . $clientException->getCode());
            } else {
                throw new \Exception("Set country error", $clientException->getCode(), $clientException);
            }
        }
    }

    public function getCountry($identity) {
        $info = $this->getIdentity($identity);
        if (!isset($info->identity) || $info->identity === null) {
            return null;
        }
        $info = $info->identity;
        if (isset($info->country) && $info->country !== null) {
            if (isset($info->country->alpha2) && $info->country->alpha2 !== null) {
                return $info->country->alpha2;
            } else if (isset($info->country->alpha3) && $info->country->alpha3 !== null) {
                return $info->country->alpha3;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
