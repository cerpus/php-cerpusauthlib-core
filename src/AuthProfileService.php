<?php

namespace Cerpus\AuthCore;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AuthProfileService
{
    private $config;
    private $integration;

    public function __construct(AuthServiceConfig $config, AuthCoreIntegration $integration) {
        $this->config = $config;
        $this->integration = $integration;
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
                'headers' => [
                    'Authorization' => 'Bearer ' . (new AccessTokenManager($this->config, $this->integration))->getAccessToken()
                ]
            ]
        );

        return $client;
    }

    public function getNames()
    {
        try {
            $response = $this->httpClient()->get('/v1/profile/name');
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

    public function setNames($firstName, $lastName, $displayName)
    {
        $response = $this->httpClient()->post('/v1/profile/name', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'
            ],
            'form_params' => [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'displayName' => $displayName
            ]
        ]);
        if ($response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function getLogins()
    {
        try {
            $response = $this->httpClient()->get('/v1/profile/logins');
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body);
            $usernames = array();
            foreach ($json as $obj) {
                $usernames[$obj->id] = array(
                    'id' => $obj->id
                );
                if (isset($obj->username)) {
                    $usernames[$obj->id]['username'] = $obj->username;
                }
            }
            return $usernames;
        } else {
            return array();
        }
    }

    public function getUsernames()
    {
        try {
            $response = $this->httpClient()->get('/v1/profile/logins');
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body);
            $usernames = array();
            foreach ($json as $obj) {
                if (isset($obj->username)) {
                    $usernames[$obj->id] = $obj->username;
                }
            }
            return $usernames;
        } else {
            return array();
        }
    }

    public function createPassword($password)
    {
        try {
            $response = $this->httpClient()->post('/v1/profile/logins', [
                'form_params' => [
                    'password' => $password
                ]
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function createUsername($username, $password)
    {
        try {
            $response = $this->httpClient()->post('/v1/profile/logins', [
                'form_params' => [
                    'username' => $username,
                    'password' => $password
                ]
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function updatePassword($usernameId, $oldPassword, $newPassword)
    {
        try {
            $response = $this->httpClient()->post('/v1/profile/logins/' . rawurlencode($usernameId) . '/password', [
                'form_params' => [
                    'oldPassword' => $oldPassword,
                    'newPassword' => $newPassword
                ]
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteUsername($usernameId)
    {
        try {
            $response = $this->httpClient()->delete('/v1/profile/logins/' . rawurlencode($usernameId));
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function getEmails()
    {
        try {
            $response = $this->httpClient()->get('/v1/profile/emails');
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

    public function addEmail($email, $validationLink)
    {
        try {
            $response = $this->httpClient()->post('/v1/profile/emails/add', [
                'form_params' => [
                    'email' => $email,
                    'validationLink' => $validationLink
                ]
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body);
            /*
             * Properties:
             *  - status: ok/confirm
             *  - id: id
             *  - verificationRequired: true/false
             */
            return $json;
        } else {
            return false;
        }
    }

    public function confirmEmail($id, $code)
    {
        try {
            $response = $this->httpClient()->post('/v1/profile/emails/confirm', [
                'form_params' => [
                    'id' => $id,
                    'code' => $code
                ]
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function resendConfirmationEmail($id, $validationLink)
    {
        try {
            $response = $this->httpClient()->post('/v1/profile/emails/' . rawurlencode($id) . '/resend', [
                'form_params' => [
                    'validationLink' => $validationLink
                ]
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteEmail($id)
    {
        try {
            $response = $this->httpClient()->delete('/v1/profile/emails/' . rawurlencode($id));
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function getPrimaryEmail()
    {
        try {
            $response = $this->httpClient()->get('/v1/profile/email');
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body);
            if (isset($json->email)) {
                return $json->email;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function setPrimaryEmail($email)
    {
        try {
            $response = $this->httpClient()->post('/v1/profile/email', [
                'form_params' => [
                    'email' => $email
                ]
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function getCountry() {
        try {
            $response = $this->httpClient()->get('/v1/profile/country');
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body);
            if (isset($json->alpha2) && $json->alpha2) {
                return $json->alpha2;
            } else if (isset($json->alpha3) && $json->alpha3) {
                return $json->alpha3;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function setCountry($countryCode) {
        try {
            $response = $this->httpClient()->post('/v1/profile/country', [
                'form_params' => [
                    'country' => $countryCode
                ]
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function unsetCountry() {
        try {
            $response = $this->httpClient()->delete('/v1/profile/country');
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = null;
            }
        }
        if ($response != null && $response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }
}
